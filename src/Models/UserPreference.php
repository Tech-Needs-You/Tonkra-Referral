<?php

namespace Tonkra\Referral\Models;

use App\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $id)
 */
class UserPreference extends Model
{
	use HasUid;

	const KEY_REFERRAL            										= 'referral';
	const KEY_REFERRAL_STATUS            							= 'referral.status';
	const KEY_REFERRAL_EMAIL_NOTIFICATION            	= 'referral.email_notification';
	const KEY_REFERRAL_SMS_NOTIFICATION            		= 'referral.sms_notification';

	protected $table = 'user_preferences';

	protected $fillable = ['user_id', 'preferences'];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'preferences'           => 'collection',
	];


	public function user(): BelongsTo
	{
		return $this->belongsTo(ReferralUser::class, 'user_id', 'id');
	}

	/**
	 * Get user preference value
	 *
	 * @param string $key // Eg. referral.email_notification
	 * 
	 * @return mixed
	 */
	public function  getPreference($key): mixed
	{
		$preferences = $this->preferences->toArray();
		$keys = explode('.', $key);

		foreach ($keys as $key) {
			if (!is_array($preferences) || !array_key_exists($key, $preferences)) {
				return null;
			}
			$preferences = $preferences[$key];
		}

		if (is_array($preferences)) {
			return collect($preferences);
		}

		return $preferences;
	}
}
