<?php

namespace Tonkra\Referral\Models;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tonkra\ReferralSystem\Models\Referral;

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


	/**
	 * Get the referral code associated with the user.
	 */
	public function referralCode()
	{
		return $this->referral?->referral_code;
	}

	public function referrer()
	{
		return $this->referral?->referrer;
	}

	public function downliners(): Builder
	{
		return User::join('referrer_user', 'users.id', '=', 'referrer_user.user_id')
			->where('referrer_user.referrer_id', $this->id)
			->select('users.*');
	}

	public function preferences(): HasOne
	{
		return $this->hasOne(UserPreference::class, 'user_id', 'id');
	}

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

	public function createUserPreference($preferences = null){
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
}