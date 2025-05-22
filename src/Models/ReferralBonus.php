<?php

namespace Tonkra\Referral\Models;

use App\Library\Traits\HasUid;
use App\Models\SubscriptionTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralBonus extends Model
{
	use HasUid;

	const STATUS_EARNED 									= 'earned';
	const STATUS_PENDING 									= 'pending';
	const STATUS_PAID 										= 'available';
	const STATUS_REJECTED 								= 'rejected';
	const STATUS_PARTLY_REDEEMED 					= 'partly redeemed';
	const STATUS_PARTLY_UNREDEEMED 				= 'partly unredeemed';
	const STATUS_REDEEMED 								= 'redeemed';

	protected $fillable = [
		'transaction_id',
		'from',
		'to',
		'bonus',
		'original_amount',
		'status',
		'paid_at'
	];

	protected $casts = [
		'bonus' => 'decimal:2',
		'paid_at' => 'datetime',
	];

	public function fromUser(): BelongsTo
	{
		return $this->belongsTo(ReferralUser::class, 'from', 'id');
	}

	public function toUser(): BelongsTo
	{
		return $this->belongsTo(ReferralUser::class, 'to', 'id');
	}

	// Scope for pending bonuses
	public function scopePending($query)
	{
		return $query->where('status', self::STATUS_PENDING);
	}

	// Scope for paid bonuses
	public function scopePaid($query)
	{
		return $query->where('status', self::STATUS_PAID);
	}

	// Scope for redeemed bonuses
	public function scopeRedeemed($query)
	{
		return $query->where('status', self::STATUS_REDEEMED);
	}

	// Scope for rejected bonuses
	public function scopeRejected($query)
	{
		return $query->where('status', self::STATUS_REJECTED);
	}

	// Scope for From
	public function scopeForFrom($query, ReferralUser $user)
	{
		return $query->where('from', $user->id);
	}

	// Scope for To
	public function scopeForTo($query, ReferralUser $user)
	{
		return $query->where('to', $user->id);
	}

	public function transaction(): BelongsTo
	{
		return $this->belongsTo(SubscriptionTransaction::class, 'transaction_id', 'id');
	}
	/**
	 * Relationship with redemptions (partial or full)
	 */
	public function redemptions(): HasMany
	{
		return $this->hasMany(ReferralRedemption::class);
	}
}
