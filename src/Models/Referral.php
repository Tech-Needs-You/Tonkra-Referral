<?php

namespace Tonkra\Referral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Tonkra\Referral\Models\ReferralUser;

class Referral extends Model
{
	use HasFactory;

	protected $fillable = ['user_id', 'referral_code', 'referred_by'];

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($referral) {
			do {
				$referral->referral_code = strtoupper(Str::random(6));
			} while (static::where('referral_code', $referral->referral_code)->exists());
		});
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(ReferralUser::class);
	}

	public static function getReferrerByReferralCode(string $code): ?self
	{
		return self::where('referral_code', $code)->first();
	}

	public function referrer(): ReferralUser|null
	{
		return ReferralUser::find($this->referred_by);
	}
}
