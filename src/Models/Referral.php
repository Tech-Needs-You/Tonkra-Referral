<?php

namespace Tonkra\Referral\Models;

use App\Library\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Tonkra\Referral\Models\ReferralUser;

class Referral extends Model
{
	use HasUid;

	const REFERRAL_STATUS 														= 'REFERRAL_STATUS';
	const REFERRAL_BONUS 															= 'REFERRAL_BONUS';
	const REFERRAL_EMAIL_NOTIFICATION 								= 'REFERRAL_EMAIL_NOTIFICATION';
	const REFERRAL_SMS_NOTIFICATION 									= 'REFERRAL_SMS_NOTIFICATION';
	const REFERRAL_DEFAULT_SENDERID 									= 'REFERRAL_DEFAULT_SENDERID';
	const REFERRAL_REDEMPTION_RATE 										= 'REFERRAL_REDEMPTION_RATE';
	const REFERRAL_MIN_SMS_REDEMPTION_STATUS 					= 'REFERRAL_MIN_SMS_REDEMPTION_STATUS';
	const REFERRAL_MIN_SMS_REDEMPTION_AMOUNT 					= 'REFERRAL_MIN_SMS_REDEMPTION_AMOUNT';
	const REFERRAL_MIN_WITHDRAWAL_REDEMPTION_STATUS 	= 'REFERRAL_MIN_WITHDRAWAL_REDEMPTION_STATUS';
	const REFERRAL_MIN_WITHDRAWAL_REDEMPTION_AMOUNT 	= 'REFERRAL_MIN_WITHDRAWAL_REDEMPTION_AMOUNT';
	const REFERRAL_MIN_TRANSFER_REDEMPTION_STATUS 		= 'REFERRAL_MIN_TRANSFER_REDEMPTION_STATUS';
	const REFERRAL_MIN_TRANSFER_REDEMPTION_AMOUNT 		= 'REFERRAL_MIN_TRANSFER_REDEMPTION_AMOUNT';
	const REFERRAL_GUIDELINES 												= 'REFERRAL_GUIDELINES';

	const PERMISSION_VIEW_REFERRAL	 									= 'view_referral';
	const PERMISSION_REFERRAL_SETTINGS	 							= 'referral settings';
	const PERMISSION_GENERAL_SETTINGS	 								= 'general settings';

	protected $fillable = ['user_id', 'referral_code', 'referred_by'];

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($referral) {
			do {
				$referral->referral_code = strtoupper(Str::random(6));
			} while (static::where('referral_code', $referral->referral_code)->exists());

			if (is_null($referral->uid)) {
				do {
					$referral->uid = uniqid();
				} while (static::where('uid', $referral->uid)->exists());
			}
		});
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public static function getReferrerByReferralCode(string $code): ?self
	{
		return self::where('referral_code', $code)->first();
	}

	public function referrer(): BelongsTo
	{
		return $this->belongsTo(User::class, 'referred_by');
	}
}
