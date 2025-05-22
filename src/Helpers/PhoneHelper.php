<?php

namespace Tonkra\Referral\Helpers;

use Illuminate\Support\Facades\Log;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneHelper
{

	protected PhoneNumberUtil $phoneUtil;

	public function __construct() {
		$this->phoneUtil = PhoneNumberUtil::getInstance();
	}
	/**
	 * Get National Number (phone without the country code).
	 *
	 * @param string|int $phone 
	 * @param bool $addLeadinZero 
	 * @return string|bool
	 */
	public function getNationalNumber($phone, $addLeadinZero = true): string|bool
	{
		try {
			$phoneNumberObject = $this->phoneUtil->parse('+' . $phone);
			if (! $this->phoneUtil->isPossibleNumber($phoneNumberObject)) {
				return false;
			}
			return $addLeadinZero?  '0' . $phoneNumberObject->getNationalNumber() : $phoneNumberObject->getNationalNumber();
		} catch (NumberParseException $e) {
			Log::error($e->getMessage());
			return false;
		}
	}

	/**
	 * Check if Number is a valid international number
	 *
	 * @param string|int $phone 
	 * @return string|bool
	 */
	public function validateInternationalNumber($phone): string|bool
	{
		try {
			$phoneNumberObject = $this->phoneUtil->parse('+' . $phone);
			if (! $this->phoneUtil->isPossibleNumber($phoneNumberObject)) {
				return false;
			}

			return ($phoneNumberObject->isItalianLeadingZero()) ? 
							$phoneNumberObject->getCountryCode() . '0' . $phoneNumberObject->getNationalNumber() : 
							$phoneNumberObject->getCountryCode() . $phoneNumberObject->getNationalNumber();
		
		} catch (NumberParseException $e) {
			Log::error($e->getMessage());
			return false;
		}
	}
}

