<?php

return [
	'status' => env('REFERRAL_STATUS', true),
	'bonus' => env('REFERRAL_BONUS', 5),
	'email_notification' => env('REFERRAL_EMAIL_NOTIFICATION', true),
	'sms_notification' => env('REFERRAL_SMS_NOTIFICATION', true),

	'default_senderid' => env('REFERRAL_DEFAULT_SENDERID', 'TONKRA SMS'),
];
