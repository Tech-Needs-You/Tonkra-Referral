<?php

namespace Tonkra\Referral\Models;

use App\Models\SubscriptionTransaction;

/**
 * @method static where(string $string, string $uid)
 * @method static create(array $array)
 */
class ReferralSubscriptionTransaction extends SubscriptionTransaction
{

	const TYPE_TOPUP = 'topup';
}
