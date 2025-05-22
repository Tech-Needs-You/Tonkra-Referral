<?php

return [
	'status' => env('REFERRAL_STATUS', true),
	'bonus' => env('REFERRAL_BONUS', 2),
	'email_notification' => env('REFERRAL_EMAIL_NOTIFICATION', true),
	'sms_notification' => env('REFERRAL_SMS_NOTIFICATION', true),

	'default_senderid' => env('REFERRAL_DEFAULT_SENDERID', 'TONKRA SMS'),
	'guidelines' => env('REFERRAL_GUIDELINES', ''),

	'redeem' => [
		'rate' =>  env('REFERRAL_REDEMPTION_RATE', 0.01),
		// 'min_amount' => env('REFERRAL_MIN_REDEMPTION_AMOUNT', 2000),
		'types' => [
			'sms_unit' => [
				'status' => env('REFERRAL_MIN_SMS_REDEMPTION_STATUS', true),
				'min_amount' => env('REFERRAL_MIN_SMS_REDEMPTION_AMOUNT', 1000),
			], 
			'withdrawal' => [
				'status' =>env('REFERRAL_MIN_WITHDRAWAL_REDEMPTION_STATUS', true),
				'min_amount' => env('REFERRAL_MIN_WITHDRAWAL_REDEMPTION_AMOUNT', 2000),
				'networks' => ['MTN', 'TELECEL', 'AIRTELTIGO'],
			],
			'transfer' => [
				'status' =>env('REFERRAL_MIN_TRANSFER_REDEMPTION_STATUS', true),
				'min_amount' => env('REFERRAL_MIN_TRANSFER_REDEMPTION_AMOUNT', 500),
			],
		],
	],

	'admin_path' => 'admin',
];
