<?php

namespace Tonkra\Referral\Facades;

use Illuminate\Support\Facades\Facade;

class ReferralSettings extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'referral-settings';
	}
}
