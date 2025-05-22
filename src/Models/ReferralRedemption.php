<?php

namespace Tonkra\Referral\Models;

use App\Library\Traits\HasUid;
use App\Models\SubscriptionTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ReferralRedemption extends Model
{
	use HasUid;

	// Status constants
	const STATUS_PENDING = 'pending';
	const STATUS_PROCESSING = 'processing';
	const STATUS_COMPLETED = 'completed';
	const STATUS_FAILED = 'failed';

	const PAYOUT_SMS = 'sms_unit';
	const PAYOUT_WALLET = 'wallet';
	const PAYOUT_BANK = 'bank_transfer';
	const PAYOUT_TRANSFER = 'transfer';

	const PAYOUT_NETWORK_MTN = 'MTN';
	const PAYOUT_NETWORK_TELECEL = 'TELECEL';
	const PAYOUT_NETWORK_AIRTELTIGO = 'AIRTELTIGO';

	protected $fillable = [
		'user_id',
		'referral_bonus_id',
		'request_id',
		'amount',
		'status',
		'payout_method',
		'payout_details',
		'failure_reason',
		'processed_at',
		'processed_by'
	];

	protected $casts = [
		'amount' => 'decimal:2',
		'payout_details' => 'array',
		'processed_at' => 'datetime'
	];

	public function user()
	{
		return $this->belongsTo(ReferralUser::class);
	}

	public function processor()
	{
		return $this->belongsTo(ReferralUser::class, 'processed_by', 'id');
	}

	public function referralBonus(): BelongsTo
	{
		return $this->belongsTo(ReferralBonus::class);
	}

	// Scopes
	public function scopePending($query)
	{
		return $query->where('status', self::STATUS_PENDING);
	}

	// Scope for processing redemptions
	public function scopeProcessing($query)
	{
		return $query->where('status', self::STATUS_PROCESSING);
	}

	public function scopeCompleted($query)
	{
		return $query->where('status', self::STATUS_COMPLETED);
	}

	public function scopeFailed($query)
	{
		return $query->where('status', self::STATUS_FAILED);
	}

	public function scopeForUser($query, $userId)
	{
		return $query->where('user_id', $userId);
	}

	/**
	 * Scope for redemptions of a specific bonus
	 */
	public function scopeForBonus($query, $bonusId)
	{
		return $query->where('referral_bonus_id', $bonusId);
	}

	// Relationship to the original transaction that created the bonus
	public function originalTransaction(): HasOneThrough
	{
		return $this->hasOneThrough(
			SubscriptionTransaction::class,
			ReferralBonus::class,
			'id', // Foreign key on referral_bonuses table
			'id', // Foreign key on subscription_transactions table
			'referral_bonus_id', // Local key on referral_redemptions table
			'transaction_id' // Local key on referral_bonuses table
		);
	}

	public function markAsProcessing()
	{
		$this->update([
			'status' => self::STATUS_PROCESSING,
			'processed_at' => now()
		]);
	}

	public function markAsCompleted(array $details = [])
	{
		$this->update([
			'status' => self::STATUS_COMPLETED,
			'payout_details' => array_merge($this->payout_details ?? [], $details),
		]);
	}

	public function markAsFailed(string $reason)
	{
		$this->update([
			'status' => self::STATUS_FAILED,
			'failure_reason' => $reason,
		]);
	}

	public function isProcessed(): bool
	{
		return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED]);
	}

	public static function generateRequestId():string
	{
		do {
			$request_id = uniqid();
		} while (ReferralRedemption::where('request_id', $request_id)->exists());

		return $request_id;
	}
}
