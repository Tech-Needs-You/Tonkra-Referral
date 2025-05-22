<?php

namespace Tonkra\Referral\Services;

use App\Library\Tool;
use App\Models\Campaigns;
use App\Models\Country;
use App\Models\Invoices;
use App\Models\Notifications;
use App\Repositories\Contracts\CampaignRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tonkra\Referral\Facades\ReferralSettings;
use Tonkra\Referral\Helpers\PhoneHelper;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Models\ReferralBonus;
use Tonkra\Referral\Models\ReferralNotification;
use Tonkra\Referral\Models\ReferralRedemption;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Models\UserPreference;
use Tonkra\Referral\Notifications\ReferralBonusNotification;

class ReferralPaymentService
{

	protected CampaignRepository $campaigns;

	/**
	 * ReferralPaymentService constructor.
	 *
	 * @param CampaignRepository      $campaigns
	 */
	public function __construct(CampaignRepository $campaigns)
	{
		$this->campaigns = $campaigns;
	}

	public function processReferralBonus(ReferralUser $user, Invoices $invoice)
	{
		$referrer = $user->referrer;
		$transaction = $invoice->transaction;

		// 2. Verify referral is eligible for bonus
		if (!$this->isEligible($user, $referrer)) {
			return false;
		}

		// 3. Calculate bonus amount
		$bonusAmount = $this->calculateBonus((float) $transaction->amount);

		// 4. Create bonus record
		$referralBonus = ReferralBonus::create([
			'transaction_id' => $transaction->id,
			'from' => $user->id,
			'to' => $referrer->id,
			'bonus' => $bonusAmount,
			'status' => ReferralBonus::STATUS_PENDING,
			'paid_at' => null,
		]);

		// 5. Process payout
		$this->processPayout($referrer, $referralBonus, $invoice);

		return $referralBonus;
	}

	protected function isEligible(ReferralUser $user, ReferralUser $referrer): bool
	{
		// Prevent self-referral
		return !($referrer->id === $user->id);
	}

	protected function calculateBonus(float $amount): float
	{
		if ($amount <= 0) {
			throw new \InvalidArgumentException('Amount must be positive');
		}
		$bonus = (float) ReferralSettings::bonus() / 100;

		return $amount * $bonus;
	}

	protected function processPayout(ReferralUser $referrer, ReferralBonus $bonus, Invoices $invoice)
	{
		$bonus->update(['status' => ReferralBonus::STATUS_PAID, 'paid_at' => now()]);

		// Send notification if enabled
		$this->sendPayoutNotification($referrer, $bonus, $invoice);
	}

	protected function sendPayoutNotification(ReferralUser $referrer, ReferralBonus $bonus, Invoices $invoice)
	{
		$phoneHelper = new PhoneHelper();
		$country = Country::where('name', ucfirst($referrer->user->customer->country))->first();
		$downliner = ReferralUser::find($bonus->from);

		Notifications::create([
			'user_id'           => $referrer->id,
			'notification_for'  => ReferralNotification::FOR_CUSTOMER,
			'notification_type' => ReferralNotification::TYPE_REFERRAL_BONUS,
			'message'           => 'Referral Bonus ('.$bonus->bonus.') from ' . $downliner->displayName(),
		]);

		$send_data = [
			'sender_id'    => ReferralSettings::defaultSenderId(),
			'sms_type'     => 'plain',
			'user'         => $referrer->user,
			'region_code'  => $country->iso_code,
			'country_code' => $country->country_code,
			'recipient'    => $phoneHelper->getNationalNumber($referrer->user->customer->phone),
			'message'      => Tool::renderTemplate(__('referral::locale.referrals.referral_bonus_sms_message'), [
				'app_name'											=> config('app.name'),
				'upliner_name'									=> $referrer->displayName(),
				'downliner_name'								=> $downliner->displayName(),
				'transaction_type'							=> $invoice->type,
				'bonus'													=> $bonus->bonus,
				'available_bonus'								=> $referrer->referralBonuses()->sum('bonus'),
				'date'													=> now(),
				'url'														=> route('referral.index')
			])
		];

		// Send referral notifications based on referrer's preferences or global config
		$notifyByEmail = (bool)($referrer->preferences?->getPreference(UserPreference::KEY_REFERRAL_EMAIL_NOTIFICATION) ?? config('referral.email_notification'));
		$notifyBySMS   = (bool)($referrer->preferences?->getPreference(UserPreference::KEY_REFERRAL_SMS_NOTIFICATION) ?? config('referral.sms_notification'));

		if ($notifyBySMS && $send_data['recipient'] && $phoneHelper->validateInternationalNumber($referrer->user->customer->phone)) {
			$this->campaigns->quickSend(new Campaigns(), $send_data);
		}

		if ($notifyByEmail) {
			$referrer->notify(new ReferralBonusNotification($bonus, $invoice, route('referral.index')));
		}
	}

	/**
	 * Calculate all referral bonus metrics with a single query
	 * 
	 * @param ReferralUser $user
	 * @return array
	 */
	public function calculateTotalRedemptions(ReferralUser $user)
	{
		$results = ReferralRedemption::where('to', $user->id)
			->selectRaw('
        SUM(CASE WHEN status = ? THEN bonus ELSE 0 END) as paid_amount,
        COUNT(CASE WHEN status = ? THEN 1 END) as paid_count,
        SUM(CASE WHEN status = ? THEN bonus ELSE 0 END) as pending_amount,
        COUNT(CASE WHEN status = ? THEN 1 END) as pending_count,
        SUM(CASE WHEN status = ? THEN bonus ELSE 0 END) as rejected_amount,
        COUNT(CASE WHEN status = ? THEN 1 END) as rejected_count,
        SUM(CASE WHEN status = ? THEN bonus ELSE 0 END) as redeemed_amount,
        COUNT(CASE WHEN status = ? THEN 1 END) as redeemed_count,
				SUM(CASE WHEN status = ? AND original_amount IS NOT NULL THEN original_amount - bonus ELSE 0 END) as partly_redeemed_amount,
				SUM(CASE WHEN status = ? AND original_amount IS NOT NULL THEN bonus ELSE 0 END) as partly_unredeemed_amount,
				SUM(CASE WHEN status = ? AND original_amount IS NOT NULL THEN 1 ELSE 0 END) as partly_redeemed_count,
        COUNT(*) as total_count,
        SUM(CASE 
            WHEN original_amount IS NOT NULL THEN original_amount 
            ELSE bonus 
        END) as total_amount
    ', [
				ReferralBonus::STATUS_PAID,
				ReferralBonus::STATUS_PAID,
				ReferralBonus::STATUS_PENDING,
				ReferralBonus::STATUS_PENDING,
				ReferralBonus::STATUS_REJECTED,
				ReferralBonus::STATUS_REJECTED,
				ReferralBonus::STATUS_REDEEMED,
				ReferralBonus::STATUS_REDEEMED,
				ReferralBonus::STATUS_PARTLY_REDEEMED,
				ReferralBonus::STATUS_PARTLY_REDEEMED,
				ReferralBonus::STATUS_PARTLY_REDEEMED,
			])
			->first();

		return [
			ReferralBonus::STATUS_PAID => [
				'amount' => $results->paid_amount + $results->partly_unredeemed_amount ?? 0,
				'count' => $results->paid_count ?? 0,
				'icon' => 'fa fa-thumbs-up text-info'
			],
			ReferralBonus::STATUS_PENDING => [
				'amount' => $results->pending_amount ?? 0,
				'count' => $results->pending_count ?? 0,
				'icon' => 'fa fa-hourglass-half text-secondary'
			],
			ReferralBonus::STATUS_REJECTED => [
				'amount' => $results->rejected_amount ?? 0,
				'count' => $results->rejected_count ?? 0,
				'icon' => 'fa fa-thumbs-down text-danger'
			],
			ReferralBonus::STATUS_REDEEMED => [
				'amount' => $results->redeemed_amount + $results->partly_redeemed_amount ?? 0,
				'count' => $results->redeemed_count ?? 0,
				'icon' => 'fa fa-star text-success'
			],
			ReferralBonus::STATUS_EARNED => [
				'amount' => $results->total_amount ?? 0,
				'count' => $results->total_count ?? 0,
				'icon' => 'fa fa-coins text-primary'
			],
			ReferralBonus::STATUS_PARTLY_REDEEMED => [
				'amount' => $results->partly_redeemed_amount ?? 0,
				'count' => $results->partly_redeemed_count ?? 0,
				'icon' => 'fa fa-star-half-stroke text-warning'
			],
			ReferralBonus::STATUS_PARTLY_UNREDEEMED => [
				'amount' => $results->partly_unredeemed_amount ?? 0,
				'count' => $results->partly_redeemed_count ?? 0,
				'icon' => 'fa fa-star-half text-dark'
			],
		];
	}

	/**
	 * Redeem referral bonuses for a user up to the specified amount
	 * 
	 * @param ReferralUser $user The user redeeming bonuses
	 * @param float $amount The amount to redeem
	 * @param string $payoutMethod The payout method (bank_transfer, sms_credit, etc.)
	 * @param array $payoutDetails Additional payout details
	 * @return array
	 * @throws \Exception
	 */
	public function redeemBonuses(ReferralUser $user, float $amount, string $payoutMethod, array $payoutDetails = []): array {
		return DB::transaction(function () use ($user, $amount, $payoutMethod, $payoutDetails) {
			$remainingAmount = $amount;
			$totalRedeemed = 0;
			$redemptions = [];
			$request_id = ReferralRedemption::generateRequestId();

			// Get pending bonuses ordered by oldest first
			$bonuses = $user->referralBonuses()
				->where('status', ReferralBonus::STATUS_PAID)
				->orWhere('status', ReferralBonus::STATUS_PARTLY_REDEEMED)
				->oldest()
				->lockForUpdate()
				->get();

			foreach ($bonuses as $bonus) {
				if ($remainingAmount <= 0) break;

				// Calculate redeemable amount (full or partial)
				$redeemAmount = min($bonus->bonus, $remainingAmount);

				// Create redemption record
				$redemption = ReferralRedemption::create([
					'user_id' => $user->id,
					'referral_bonus_id' => $bonus->id,
					'request_id' => $request_id,
					'amount' => $redeemAmount,
					'payout_method' => $payoutMethod,
					'payout_details' => $payoutDetails,
					'status' => ReferralRedemption::STATUS_PENDING
				]);

				try {
					if ($redeemAmount == $bonus->bonus) {
						$updateData = [
							'status' => ReferralBonus::STATUS_REDEEMED
						];

						if($bonus->status == ReferralBonus::STATUS_PARTLY_REDEEMED && !is_null($bonus->original_amount)){
							$updateData['bonus'] = $bonus->original_amount;
							$updateData['original_amount'] = null;
						}
						
						$bonus->update($updateData);
					} else {
						$bonus->update([
							'original_amount' => $bonus->original_amount ?? $bonus->bonus,
							'bonus' => $bonus->bonus - $redeemAmount,
							'status' => ReferralBonus::STATUS_PARTLY_REDEEMED,
						]);
					}

					$result = $this->processRedemptionPayout($user, $redemption, $redeemAmount, $payoutMethod, $payoutDetails);

					$remainingAmount -= $redeemAmount;
					$totalRedeemed += $redeemAmount;
					$redemptions[] = $redemption;

					// Send notification
					$this->sendRedemptionNotification($redemption, true);
				} catch (\Exception $e) {
					DB::rollBack();
					logger()->error("Transfer failed: " . $e->getMessage());
					return false;
				}

			}

			DB::commit();

			return [
				'success' => $totalRedeemed > 0,
				'total_redeemed' => $totalRedeemed,
				'moneytary_value' => number_format($totalRedeemed * ReferralSettings::redeemRate(), 2),
				'remaining_amount' => max($remainingAmount, 0),
				'redemptions' => $redemptions,
				'available_bonus' => $user->paidReferralBonuses()->sum('bonus')
			];
		});
	}

	protected function processRedemptionPayout(ReferralUser $user, ReferralRedemption $redemption, float $amount, string $method, array $details)
	{
		switch ($method) {
			case ReferralRedemption::PAYOUT_SMS:
				return $this->processSmsCredit($user, $redemption, $amount, $details);
			case ReferralRedemption::PAYOUT_WALLET:
				return $this->processWalletCredit($user, $redemption, $amount, $details);
			case ReferralRedemption::PAYOUT_TRANSFER:
				return $this->processTransfer($user, $redemption, $amount, $details);
			default:
				throw new \Exception("Invalid payout method: {$method}");
		}
	}

	protected function processSmsCredit(ReferralUser $user, ReferralRedemption $redemption, float $amount, array $details)
	{
		$unit_before = $user->user->sms_unit;
		$user->user->increment('sms_unit', $amount);

		return $redemption->update([
			'status' => ReferralRedemption::STATUS_COMPLETED,
			'processed_at' => now(),
			'payout_details' => array_merge($details, [
				'reference' => 'SMS-' . uniqid(),
				'processed_at' => now()->toDateTimeString(),
				'user_sms_unit_before' => $unit_before,
				'user_sms_unit_after' => $user->user->unit_sms,
			])
		]);
	}

	protected function processWalletCredit(ReferralUser $user, ReferralRedemption $redemption, float $amount, array $details)
	{
		return $redemption->update([
			'status' => ReferralRedemption::STATUS_PENDING,
			'payout_details' => array_merge($details, [
				'reference' => 'WALLET-' . uniqid(),
				'user_sms_unit' => $user->user->sms_unit,
				'monetary_value' => number_format($amount * ReferralSettings::redeemRate(), 2),
				'redemption_rate' => ReferralSettings::redeemRate()
			])
		]);
	}

	protected function processTransfer(ReferralUser $user, ReferralRedemption $redemption, float $amount, array $details)
	{
		$user_sms_unit = $user->user->sms_unit;
		$referralCode = $details['recipient'];
		$recipient = Referral::findByReferralCode($referralCode)->user;
		$recipient_sms_unit_before = $recipient->sms_unit;

		$recipient->sms_unit = $recipient->sms_unit + $amount;
		$recipient->save();

		return $redemption->update([
			'status' => ReferralRedemption::STATUS_COMPLETED,
			'processed_at' => now(),
			'payout_details' => array_merge($details, [
				'reference' => 'TRANSFER-' . uniqid(),
				'processed_at' => now()->toDateTimeString(),
				'user_sms_unit' => $user_sms_unit,
				'recipient_sms_unit_before' => $recipient_sms_unit_before,
				'recipient_sms_unit_after' => $recipient->sms_unit,
			])
		]);
	}

	protected function sendRedemptionNotification(ReferralRedemption $redemption, bool $success)
	{
		// Implement your notification logic (email, SMS, etc.)
		// Similar to what was in the job version
	}

	public function updateRedemptionStatus(Collection $redemptions, array $validated)
	{
		foreach ($redemptions as $redemption) {
			$redemption->update([
				'status' => $validated['status'],
				'processed_by' => Auth::user()->id,
				'processed_at' => now()->toDateTimeString(),
				'payout_details' => array_merge($redemption->payout_details, [
					'amount_paid' => $redemption->payout_details['moneytary_value'] ?? number_format($redemption->amount * ReferralSettings::redeemRate(), 2),
				])
			]);
		}
		return true;
	}
}
