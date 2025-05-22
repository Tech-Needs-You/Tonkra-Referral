<?php

namespace Tonkra\Referral\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Http\JsonResponse;
use Tonkra\Referral\Models\Referral;

class ReferralUser extends User
{
	protected $table = 'users';

	/**
	 * Define a relationship with the Referral model.
	 */
	public function referral()
	{
		return $this->hasOne(Referral::class, 'user_id', 'id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'id');
	}

	public function referralBonuses(): HasMany
	{
		return $this->hasMany(ReferralBonus::class, 'to', 'id');
	}

	public function paidReferralBonuses(): HasMany
	{
		return $this->hasMany(ReferralBonus::class, 'to')
			->where('status', ReferralBonus::STATUS_PAID)
			->orWhereNotNull('original_amount');
	}

	public function referralBonusesFromUser(ReferralUser $fromUser): HasMany
	{
		return $this->referralBonuses()->where('from', $fromUser->id);
	}

	/**
	 * Calculate the total earned bonus from all referral activities
	 * 
	 * @return float
	 */
	public function totalEarnedBonus(): float
	{
		return $this->referralBonuses()
			->selectRaw('SUM(COALESCE(original_amount, bonus)) as total_bonus')
			->value('total_bonus') ?? 0;
	}

	public function totalEarnedBonusFromUser(ReferralUser $referralUser): float
	{
		return $this->referralBonuses()
			->where('from', $referralUser->id)
			->selectRaw('SUM(COALESCE(original_amount, bonus)) as bonus')
			->value('bonus') ?? 0;
	}

	/**
	 * Calculate all referral bonus metrics with a single query
	 * 
	 * @return array
	 */
	public function referralBonusStats()
	{
		$results = $this->referralBonuses()
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
				'icon' => 'far fa-thumbs-up text-info'
			],
			ReferralBonus::STATUS_PENDING => [
				'amount' => $results->pending_amount ?? 0,
				'count' => $results->pending_count ?? 0,
				'icon' => 'far fa-hourglass-half text-secondary'
			],
			ReferralBonus::STATUS_REJECTED => [
				'amount' => $results->rejected_amount ?? 0,
				'count' => $results->rejected_count ?? 0,
				'icon' => 'far fa-thumbs-down text-danger'
			],
			ReferralBonus::STATUS_REDEEMED => [
				'amount' => $results->redeemed_amount + $results->partly_redeemed_amount ?? 0,
				'count' => $results->redeemed_count ?? 0,
				'icon' => 'fas fa-star text-success'
			],
			ReferralBonus::STATUS_EARNED => [
				'amount' => $results->total_amount ?? 0,
				'count' => $results->total_count ?? 0,
				'icon' => 'fas fa-coins text-primary'
			],
			ReferralBonus::STATUS_PARTLY_REDEEMED => [
				'amount' => $results->partly_redeemed_amount ?? 0,
				'count' => $results->partly_redeemed_count ?? 0,
				'icon' => 'fas fa-star-half-stroke text-warning'
			],
			ReferralBonus::STATUS_PARTLY_UNREDEEMED => [
				'amount' => $results->partly_unredeemed_amount ?? 0,
				'count' => $results->partly_redeemed_count ?? 0,
				'icon' => 'fas fa-star-half text-dark'
			],
		];
	}

	/**
	 * Calculate all referral bonus demption metrics with a single query
	 * 
	 * @return array
	 */
	public function referralBonusRedemptionStats()
	{
		$results = $this->referralRedemptions()
			->selectRaw('
        SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as pending,
        SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as processing,
        SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as completd,
        SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as failed,
        SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as sms_unit_payouts,
        SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as wallet_payouts,
        SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as bank_transfer_payouts,
        SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as transfer_payouts
    ', [
				ReferralRedemption::STATUS_PENDING,
				ReferralRedemption::STATUS_PROCESSING,
				ReferralRedemption::STATUS_COMPLETED,
				ReferralRedemption::STATUS_FAILED,
				ReferralRedemption::PAYOUT_SMS,
				ReferralRedemption::PAYOUT_WALLET,
				ReferralRedemption::PAYOUT_BANK,
				ReferralRedemption::PAYOUT_TRANSFER,
			])
			->first();

		return [
			ReferralRedemption::STATUS_PENDING => [
				'amount' => $results->pending ?? 0,
				'icon' => 'fa fa-thumbs-up text-info',
				'color' => 'info'
			],
			ReferralRedemption::STATUS_PROCESSING => [
				'amount' => $results->processing ?? 0,
				'icon' => 'fa fa-hourglass-half text-secondary',
				'color' => 'secondary'
			],
			ReferralRedemption::STATUS_COMPLETED => [
				'amount' => $results->completd ?? 0,
				'icon' => 'fa fa-thumbs-down text-danger',
				'color' => 'danger'
			],
			ReferralRedemption::STATUS_FAILED => [
				'amount' => $results->failed ?? 0,
				'icon' => 'fa fa-ban text-danger',
				'color' => 'danger'
			],
			ReferralRedemption::PAYOUT_SMS => [
				'amount' => $results->sms_unit_payouts ?? 0,
				'icon' => 'fa fa-comment-sms text-primary',
				'color' => 'primary'
			],
			ReferralRedemption::PAYOUT_WALLET => [
				'amount' => $results->wallet_payouts ?? 0,
				'icon' => 'fa fa-wallet text-danger',
				'color' => 'danger'
			],
			ReferralRedemption::PAYOUT_BANK => [
				'amount' => $results->bank_transfer_payouts ?? 0,
				'icon' => 'fa fa-vault text-dark',
				'color' => 'dark'
			],
			ReferralRedemption::PAYOUT_TRANSFER => [
				'amount' => $results->transfer_payouts ?? 0,
				'icon' => 'fa fa-share-nodes text-info',
				'color' => 'info'
			],
		];
	}

	public function adminReferralRedemptionStats()
	{
		$results = ReferralRedemption::selectRaw('
            SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as processing,
            SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as completed,
            SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as failed,
            SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as sms_unit_payouts,
            SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as wallet_payouts,
            SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as bank_transfer_payouts,
            SUM(CASE WHEN payout_method = ? THEN amount ELSE 0 END) as transfer_payouts,
            COUNT(DISTINCT user_id) as total_users
        ', [
				ReferralRedemption::STATUS_PENDING,
				ReferralRedemption::STATUS_PROCESSING,
				ReferralRedemption::STATUS_COMPLETED,
				ReferralRedemption::STATUS_FAILED,
				ReferralRedemption::PAYOUT_SMS,
				ReferralRedemption::PAYOUT_WALLET,
				ReferralRedemption::PAYOUT_BANK,
				ReferralRedemption::PAYOUT_TRANSFER,
			])
			->first();

		return [
			'status_stats' => [
				ReferralRedemption::STATUS_PENDING => [
					'amount' => $results->pending ?? 0,
					'icon' => 'fa fa-clock text-info',
					'color' => 'info'
				],
				ReferralRedemption::STATUS_PROCESSING => [
					'amount' => $results->processing ?? 0,
					'icon' => 'fa fa-cog fa-spin text-warning',
					'color' => 'warning'
				],
				ReferralRedemption::STATUS_COMPLETED => [
					'amount' => $results->completed ?? 0,
					'icon' => 'fa fa-check-circle text-success',
					'color' => 'success'
				],
				ReferralRedemption::STATUS_FAILED => [
					'amount' => $results->failed ?? 0,
					'icon' => 'fa fa-times-circle text-danger',
					'color' => 'danger'
				],
			],
			'payout_stats' => [
				ReferralRedemption::PAYOUT_SMS => [
					'amount' => $results->sms_unit_payouts ?? 0,
					'icon' => 'fa fa-comment-alt text-primary',
					'color' => 'primary'
				],
				ReferralRedemption::PAYOUT_WALLET => [
					'amount' => $results->wallet_payouts ?? 0,
					'icon' => 'fa fa-wallet text-success',
					'color' => 'success'
				],
				ReferralRedemption::PAYOUT_BANK => [
					'amount' => $results->bank_transfer_payouts ?? 0,
					'icon' => 'fa fa-university text-dark',
					'color' => 'dark'
				],
				ReferralRedemption::PAYOUT_TRANSFER => [
					'amount' => $results->transfer_payouts ?? 0,
					'icon' => 'fa fa-exchange-alt text-info',
					'color' => 'info'
				],
			],
			'user_stats' => [
				'total_users' => $results->total_users ?? 0,
				'icon' => 'fa fa-users text-secondary',
				'color' => 'secondary'
			]
		];
	}

	public function referralRedemptions()
	{
		return $this->hasMany(ReferralRedemption::class, 'user_id', 'id');
	}

	// Relationship for pending redemptions
	public function pendingRedemptions(): HasMany
	{
		return $this->referralRedemptions()->where('status', ReferralRedemption::STATUS_PENDING);
	}


	// Relationship for completed redemptions
	public function completedRedemptions(): HasMany
	{
		return $this->referralRedemptions()->where('status', ReferralRedemption::STATUS_COMPLETED);
	}

	// Relationship for failed redemptions
	public function failedRedemptions(): HasMany
	{
		return $this->referralRedemptions()->where('status', ReferralRedemption::STATUS_FAILED);
	}

	/**
	 * Get the referral code associated with the user.
	 */
	public function referralCode()
	{
		return $this->referral()->firstOrCreate([], [
			'referred_by' => null
		])->referral_code;
	}

	public function referrer(): HasOneThrough
	{
		return $this->hasOneThrough(
			ReferralUser::class,
			Referral::class,
			'user_id',       // Foreign key on referrals table
			'id',            // Foreign key on users table  
			'id',            // Local key on users table
			'referred_by'    // Local key on referrals table
		);
	}

	public function downliners()
	{
		return $this->hasManyThrough(
			ReferralUser::class,  // Final model (users)
			Referral::class,      // Intermediate model (referrals)
			'referred_by',        // Foreign key on referrals table (points to users)
			'id',                 // Foreign key on users table
			'id',                 // Local key on ReferralUser (users table)
			'user_id'             // Local key on referrals table
		);
	}

	public function preferences(): HasOne
	{
		return $this->hasOne(UserPreference::class, 'user_id', 'id');
	}

	// public function totalEarnedBonuses()
	// {
	// 	return $this->referralBonuses()
	// 		->whereNotNull('original_amount')
	// 		->selectRaw('SUM(original_amount) as total')
	// 		->value('total') ?? 0;
	// }

	// public function totalRedeemedBonuses()
	// {
	// 	return $this->hasMany(ReferralRedemption::class)
	// 		->where('status', 'processed')
	// 		->sum('amount');
	// }

	public function getPreferencesAttribute()
	{
		return $this->preferences()->firstOrCreate([], [
			"preferences" => [
				"referral" => [
					'status' => config('referral.status'),
					'bonus' => config('referral.bonus'),
					'email_notification' => config('referral.email_notification'),
					'sms_notification' => config('referral.sms_notification'),
				]
			]
		]);
	}

	public function referralLink(): string
	{
		return route('referral.register.with_referrer', ['referrer' => $this->referralCode()]);
	}

	public function createUserPreference($preferences = null)
	{
		$preferences = ($preferences == null) ? [
			"referral" => [
				'status' => config('referral.status'),
				'bonus' => config('referral.bonus'),
				'email_notification' => config('referral.email_notification'),
				'sms_notification' => config('referral.sms_notification'),
			]
		] : $preferences;

		return $this->preferences()->updateOrCreate(
			[],
			['preferences' => $preferences]
		);
	}

	/**
	 * Save user preferences 
	 *
	 * @param  array  $preferences
	 *
	 * @return JsonResponse
	 * @throws AuthorizationException
	 */
	public function savePreference(array $preferences, $key): JsonResponse
	{

		if (config('app.stage') == 'demo') {
			return response()->json([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		$user_preferences = $this->preferences;
		$new_key_preferences[$key] = $preferences;

		if (!$user_preferences) {
			$this->preferences()->create([
				'preferences' => $new_key_preferences,
			]);

			return response()->json([
				'status'  => 'success',
				'message' => ucfirst(__('referral::locale.preferences.preference_saved_succeessfully', ['key' => $key])),
			]);
		}

		$this->preferences()->update([
			'preferences' => $user_preferences->preferences->isEmpty() ? $new_key_preferences : array_merge($user_preferences->preferences->toArray(), $new_key_preferences),
		]);

		return response()->json([
			'status'  => 'success',
			'message' => ucfirst(__('referral::locale.preferences.preference_saved_succeessfully', ['key' => $key])),
		]);
	}

}
