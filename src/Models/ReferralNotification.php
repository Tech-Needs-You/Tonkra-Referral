<?php

namespace Tonkra\Referral\Models;

use App\Models\Notifications;

/**
 * @method static where(string $string, string $uid)
 * @method static create(array $array)
 */
class ReferralNotification extends Notifications
{
	const FOR_ADMIN                                 = 'admin';
	const FOR_CUSTOMER                              = 'customer';

	const TYPE_SUBSCRIPTION                         = 'subscription';
	const TYPE_CAMPAIGN                             = 'campaign';
	const TYPE_PLAN                                 = 'plan';
	const TYPE_SENDER_ID                            = 'senderid';
	const TYPE_USER                                 = 'user';
	const TYPE_NEW_REFERRAL                         = 'new_referral';
	const TYPE_REFERRAL_BONUS                       = 'referral_bonus';
	const TYPE_TOPUP                                = 'top_up';

	const MARK_READ                                 = '1';
	const MARK_UNREAD                               = '0';
}
