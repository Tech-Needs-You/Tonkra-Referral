<?php

namespace Tonkra\Referral\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class Phone implements ValidationRule
{

	/**
	 *
	 * @param string  $attribute
	 * @param mixed   $value
	 * @param Closure $fail
	 * @return void
	 */
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
		$checkNumeric = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-. \\\/]?)?((?:\(?\d+\)?[\-. \\\/]?)*)(?:[\-. \\\/]?(?:#|ext\.?|extension|x)[\-. \\\/]?(\d+))?$%i', $value) && strlen($value) >= 7 && strlen($value) <= 17;

		if (! $checkNumeric) {
			$fail(__('referral::locale.validations.invalid_phone_number', ['phone' => $value]));
		}

		try {
			$phoneUtil         = PhoneNumberUtil::getInstance();
			$phoneNumberObject = $phoneUtil->parse('+' . request('country_code') . $value);
			$countryCode       = $phoneNumberObject->getCountryCode();
			$isoCode           = $phoneUtil->getRegionCodeForNumber($phoneNumberObject);

			if (! $phoneUtil->isPossibleNumber($phoneNumberObject) || empty($countryCode) || empty($isoCode)) {
				$fail(__('referral::locale.validations.invalid_phone_number', ['phone' => $value]));
			}
		} catch (NumberParseException) {
			$fail(__('referral::locale.validations.invalid_phone_number', ['phone' => $value]));
		}
	}
}
