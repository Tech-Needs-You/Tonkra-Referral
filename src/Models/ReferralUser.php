<?php

namespace Tonkra\Referral\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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


	/**
	 * Get the referral code associated with the user.
	 */
	public function referralCode()
	{
		return $this->referral?->referral_code;
	}

	public function referrer()
	{
		return $this->referral?->referrer();
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
