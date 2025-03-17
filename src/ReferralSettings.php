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
}
