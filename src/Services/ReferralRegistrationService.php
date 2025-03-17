<?php

namespace Tonkra\Referral\Services;

use App\Exceptions\GeneralException;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use YourPackage\Models\Referral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Rules\Phone;
use Validator;
use YourPackage\Exceptions\ReferralException;

class ReferralRegistrationService
{

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param array $data
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validate(array $data): \Illuminate\Contracts\Validation\Validator
	{
		$rules = [
			'first_name' 				=> ['required', 'string', 'min:3', 'max:255'],
			'last_name'	 				=> ['required', 'string', 'min:3', 'max:255'],
			'email'      				=> ['required', 'string', 'email', 'max:255', 'unique:users'],
			'password'   				=> ['required', 'string', 'min:8', 'confirmed'],
			'phone'      				=> ['required', new Phone($data['phone'])],
			'timezone'   				=> ['required', 'timezone'],
			'address'    				=> ['string', ' nullable '],
			'city'       				=> ['string', 'nullable'],
			'country'    				=> ['required', 'string', 'exists:countries,name'],
			'country_code' 			=> ['required'],
			'locale'     				=> ['required', 'string', 'min:2', 'max:2'],
			'referrer'   				=> ['nullable', 'string', 'exists:referrals,referral_code'],
		];

		if (config('no-captcha.registration')) {
			$rules['g-recaptcha-response'] = 'required|recaptchav3:register,0.5';
		}

		$messages = [
			'g-recaptcha-response.required'    => __('locale.auth.recaptcha_required'),
			'g-recaptcha-response.recaptchav3' => __('locale.auth.recaptcha_required'),
		];

		return Validator::make($data, $rules, $messages);
	}
	/**
	 * set default values for other required fields
	 *
	 * @param Request $request
	 * @return array|RedirectResponse
	 */
	public function setDefaults(Request $request): array|RedirectResponse
	{
		$data = $request->except('_token');
		$country = Country::where('country_code', str_replace(['(', ')', '+', '-', ' '], '', $data['country_code']))->first();

		$data['timezone'] = config('app.timezone');
		$data['country'] = $country->name;
		$data['locale'] = (isset($data['locale'])) ? $data['locale'] : config('app.locale');
		$data['address'] = null;
		$data['city'] = null;
		$data['postcode'] = null;
		$data['company'] = null;

		return [$data, $country];
	}

	/**
	 * @throws Exception
	 * @throws Throwable
	 */
	public function register(array $input): ReferralUser
	{
		throw_if(
			! config('account.can_register'),
			new GeneralException(__('locale.exceptions.registration_disabled'))
		);

		$user = $this->users->store([
			'first_name'  => $input['first_name'],
			'last_name'   => $input['last_name'],
			'email'       => $input['email'],
			'password'    => $input['password'],
			'status'      => true,
			'phone'       => null,
			'is_customer' => true,
		], true);

		Customer::updateOrCreate(
			['user_id' => $user->id],
			collect($input)->only(['phone', 'address', 'company', 'city', 'postcode', 'country'])->toArray()
		);

		Notifications::create([
			'user_id'           => 1,
			'notification_for'  => 'admin',
			'notification_type' => 'user',
			'message'           => "{$user->displayName()} Registered",
		]);

		Auth::login($user, true);

		return $user;
	}
}