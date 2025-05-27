<?php

namespace Tonkra\Referral\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tonkra\Referral\Models\Referral;

class StoreReferralSettingsRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return $this->user()->can(Referral::PERMISSION_REFERRAL_SETTINGS);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules()
	{
		return [
			'status' => 'required|boolean',
			'bonus' => 'required|numeric',
			'email_notification' => 'required|boolean',
			'sms_notification' => 'required|boolean',
		];
	}
}
