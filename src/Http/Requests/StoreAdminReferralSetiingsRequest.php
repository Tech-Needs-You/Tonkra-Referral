<?php

namespace Tonkra\Referral\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tonkra\Referral\Models\Referral;

class StoreAdminReferralSetiingsRequest extends FormRequest
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
			'status' => 'in:true,false,on,off',
			'bonus' => 'required|numeric',
			'email_notification' => 'required|boolean',
			'sms_notification' => 'required|boolean',
			'default_senderid' => 'required|exists:senderid,sender_id',
			'guideline' => 'required',
			'min_sms_redemption_status' => 'in:true,false,on,off',
			'min_sms_redemption_amount' => 'required|min:0',
			'min_withdrawal_redemption_status' => 'in:true,false,on,off',
			'min_withdrawal_redemption_amount' => 'required|min:0',
			'min_transfer_redemption_status' => 'in:true,false,on,off',
			'min_transfer_redemption_amount' => 'required|min:0',
		];
	}
}
