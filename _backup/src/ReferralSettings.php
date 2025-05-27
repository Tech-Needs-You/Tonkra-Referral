<?php

namespace Tonkra\Referral;

class ReferralSettings
{
	public function status()
	{
		return config('referral.status');
	}

	public function bonus()
	{
		return config('referral.bonus');
	}

	public function smsNotification()
	{
		return config('referral.sms_notification');
	}

	public function emailNotification()
	{
		return config('referral.email_notification');
	}

	public function defaultSenderId()
	{
		return config('referral.default_senderid');
	}

	public function redeemRate()
	{
		return config('referral.redeem.rate');
	}

	public function minSmsRedeemStatus()
	{
		return config('referral.redeem.types.sms_unit.status');
	}

	public function minSmsRedeemAmount()
	{
		return config('referral.redeem.types.sms_unit.min_amount');
	}

	public function minWithdrawalRedeemStatus()
	{
		return config('referral.redeem.types.withdrawal.status');
	}

	public function minWithdrawalRedeemAmount()
	{
		return config('referral.redeem.types.withdrawal.min_amount');
	}

	public function minTransferRedeemStatus()
	{
		return config('referral.redeem.types.transfer.status');
	}

	public function minTransferRedeemAmount()
	{
		return config('referral.redeem.types.transfer.min_amount');
	}

	public function withdrawalNetworks()
	{
		return config('referral.redeem.types.withdrawal.networks');
	}

	public function redeemTypes()
	{
		return config('referral.redeem.types');
	}
}
