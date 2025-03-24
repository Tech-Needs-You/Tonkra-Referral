<?php

namespace Tonkra\Referral\Http\Controllers;

use App\Library\Tool;
use App\Models\Campaigns;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Notifications;
use App\Models\PaymentMethods;
use App\Models\Plan;
use App\Models\Senderid;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionTransaction;
use App\Notifications\WelcomeEmailNotification;
use App\Repositories\Contracts\CampaignRepository;
use Carbon\Carbon;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Tonkra\Referral\Facades\ReferralSettings;
use Tonkra\Referral\Helpers\PhoneHelper;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Repositories\Contracts\ReferralAccountRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralSubscriptionRepository;
use Tonkra\Referral\Services\ReferralRegistrationService;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Models\ReferralNotification;
use Tonkra\Referral\Models\UserPreference;
use Tonkra\Referral\Notifications\NewReferralNotification;
use Tonkra\Referral\Notifications\NewUserNotification;

class ReferralRegisterController extends Controller
{
	/**
	 * Where to redirect users after registration.
	 *
	 * @var string
	 */
	protected string $redirectTo = '/login';

	/**
	 * @var ReferralAccountRepository
	 */
	protected ReferralAccountRepository $account;

	protected ReferralSubscriptionRepository $subscriptions;

	protected CampaignRepository $campaigns;

	protected ReferralRegistrationService $registrationService;

	/**
	 * RegisterController constructor.
	 *
	 * @param ReferralAccountRepository      $account
	 * @param ReferralSubscriptionRepository $subscriptions
	 */
	public function __construct(ReferralAccountRepository $account, ReferralSubscriptionRepository $subscriptions, CampaignRepository $campaigns, ReferralRegistrationService $registrationService)
	{
		$this->middleware('guest');
		$this->account       = $account;
		$this->subscriptions = $subscriptions;
		$this->campaigns = $campaigns;
		$this->registrationService = $registrationService;
	}

	public function show($referrer = null): View|Factory|Application
	{
		$isReferralEnabled = ReferralSettings::status();
		if (!$isReferralEnabled) {
			$referrer = null;
		}

		$pageConfigs     = ['blankPage' => true,];
		$languages       = Language::where('status', 1)->get();
		$plans           = Plan::where('status', true)->where('show_in_customer', true)->cursor();
		$payment_methods = PaymentMethods::where('status', 1)->get();

		return view('referral::register', [
			'pageConfigs'     => $pageConfigs,
			'languages'       => $languages,
			'plans'           => $plans,
			'payment_methods' => $payment_methods,
			'referrer'              => $referrer,
			'isReferralEnabled'     => $isReferralEnabled,
			'with_referrer'         => is_null($referrer) ? false : true,
		]);
	}

	public function register(Request $request, $referrer = null): View|Factory|Application|RedirectResponse
	{
		if (config('app.stage') == 'demo') {
			return redirect()->route('login')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		$redirectRoute = is_null($referrer)
			? route('referral.register')
			: route('referral.register.with_referrer', $referrer);

		[$data, $country] = $this->registrationService->setDefaults($request);

		$v = $this->registrationService->validate($data);

		if ($v->fails()) {
			return redirect()->to($redirectRoute)
				->withInput()
				->withErrors($v->errors());
		}

		$phone = str_replace(['(', ')', '+', '-', ' '], '', $data['country_code'] . $data['phone']);
		$plan = Plan::where('price', '0')->first() ?? Plan::create([
			'user_id'              => 1,
			'currency_id'          => Currency::where('code', 'GHS')->value('id'),
			'name'                 => 'Free',
			'description'          => 'A simple start for everyone',
			'price'                => 0,
			'billing_cycle'        => 'non_expiry',
			'frequency_amount'     => 9999,
			'frequency_unit'       => 'year',
			'options'              => '{"sms_max":"5","list_max":"5","subscriber_max":"500","subscriber_per_list_max":"100","segment_per_list_max":"3","billing_cycle":"non_expiry","sending_limit":"50000_per_hour","sending_quota":"100","sending_quota_time":"1","sending_quota_time_unit":"hour","max_process":"1","list_import":"yes","list_export":"yes","api_access":"no","create_sub_account":"no","delete_sms_history":"no","add_previous_balance":"no","sender_id_verification":"yes","send_spam_message":"no"}',
			'status'               => true,
			'is_popular'           => false,
			'tax_billing_required' => false,
			'show_in_customer'     => true,
			'is_dlt'               => false,
		]);

		$phoneHelper = new PhoneHelper();

		$data['phone'] = $phoneHelper->validateInternationalNumber($phone);
		if (!$data['phone']) {
			return redirect()->to($redirectRoute)->with([
				'status'  => 'error',
				'message' => __('referral::locale.validations.invalid_phone_number', $phone),
			]);
		}

		$user = $this->account->register($data);

		$admin = ReferralUser::find(1);

		if ($plan->price == 0.00) {
			$subscription                         = new Subscription();

			$subscription->user_id                = $user->id;
			$subscription->start_at               = Carbon::now();
			$subscription->status                 = Subscription::STATUS_ACTIVE;
			$subscription->plan_id                = $plan->getBillableId();
			$subscription->end_period_last_days   = '10';
			$endDate = $subscription->getPeriodEndsAt(Carbon::now());
			$subscription->current_period_ends_at = $endDate->year > 9999 ? Carbon::create(9999, 12, 31, 23, 59, 59) : $endDate;
			$subscription->end_at                 = null;
			$subscription->end_by                 = null;
			$subscription->payment_method_id      = null;

			$subscription->save();

			// add transaction
			$subscription->addTransaction(SubscriptionTransaction::TYPE_SUBSCRIBE, [
				'end_at'                 => $subscription->end_at,
				'current_period_ends_at' => $subscription->current_period_ends_at,
				'status'                 => SubscriptionTransaction::STATUS_SUCCESS,
				'title'                  => trans('locale.subscription.subscribed_to_plan', ['plan' => $subscription->plan->getBillableName()]),
				'amount'                 => $subscription->plan->getBillableFormattedPrice(),
			]);

			// add log
			$subscription->addLog(SubscriptionLog::TYPE_ADMIN_PLAN_ASSIGNED, [
				'plan'  => $subscription->plan->getBillableName(),
				'price' => $subscription->plan->getBillableFormattedPrice(),
			]);

			$user->sms_unit = $plan->getOption('sms_max');
			$user->save();
			$preferences = $user->createUserPreference();

			// add referrer if referral system is enabled
			if (config('referral.status')) {
				$referrer = null;
				if (!empty($data['referrer'])) {
					$referrer = Referral::getReferrerByReferralCode($data['referrer']);
				}

				$referral = Referral::create([
					'user_id'     => $user->id,
					'referred_by' => $referrer?->id,
				]);

				if ($referrer) {
					Notifications::create([
						'user_id'           => $referrer->id,
						'notification_for'  => ReferralNotification::FOR_CUSTOMER,
						'notification_type' => ReferralNotification::TYPE_NEW_REFERRAL,
						'message'           => 'New Referral Registered for ' . $user->displayName(),
					]);

					$send_data = [
						'sender_id'    => config('referral.default_senderid'),
						'sms_type'     => 'plain',
						'user'         => $referrer->user,
						'region_code'  => $country->iso_code,
						'country_code' => $country->country_code,
						'recipient'    => $phoneHelper->getNationalNumber($referrer->user->customer->phone),
						'message'      => Tool::renderTemplate(__('referral::locale.referrals.new_referral_sms_message'), [
							'upliner_name'   => $referrer->displayName(),
							'downliner_name' => $user->displayName(),
							'app_name'       => config('app.name'),
						])
					];

					// Send referral notifications based on referrer's preferences or global config
					$notifyByEmail = (bool)($referrer->preferences?->getPreference(UserPreference::KEY_REFERRAL_EMAIL_NOTIFICATION) ?? config('referral.email_notification'));
					$notifyBySMS   = (bool)($referrer->preferences?->getPreference(UserPreference::KEY_REFERRAL_SMS_NOTIFICATION) ?? config('referral.sms_notification'));

					if ($notifyByEmail) {
						$referrer->notify(new NewReferralNotification($user->user, route('user.account', ['tab' => 'referral'])));
					}

					if ($notifyBySMS && $send_data['recipient'] && $phoneHelper->validateInternationalNumber($referrer->user->customer->phone)) {
						$this->campaigns->quickSend(new Campaigns(), $send_data);
					}
				}
			}

			// notify admin of new user registration
			$admin->notify(new NewUserNotification($user->user, $referrer, route('admin.customers.index')));

			if (config('account.verify_account')) {
				$user->sendEmailVerificationNotification();
			} else {
				if (Helper::app_config('user_registration_notification_email')) {
					$user->notify(new WelcomeEmailNotification($user->first_name, $user->last_name, $user->email, route('login'), $data['password']));
				}
			}

			if (isset($plan->getOptions()['sender_id']) && $plan->getOption('sender_id') !== null) {
				$sender_id = Senderid::where('sender_id', $plan->getOption('sender_id'))->where('user_id', $user->id)->first();
				if (! $sender_id) {
					$current = Carbon::now();
					Senderid::create([
						'sender_id'        => $plan->getOption('sender_id'),
						'status'           => 'active',
						'price'            => $plan->getOption('sender_id_price'),
						'billing_cycle'    => $plan->getOption('sender_id_billing_cycle'),
						'frequency_amount' => $plan->getOption('sender_id_frequency_amount'),
						'frequency_unit'   => $plan->getOption('sender_id_frequency_unit'),
						'currency_id'      => $plan->currency->id,
						'validity_date'    => $current->add($plan->getOption('sender_id_frequency_unit'), $plan->getOption('sender_id_frequency_amount')),
						'payment_claimed'  => true,
						'user_id'          => $user->id,
					]);
				}
			}

			return redirect()->route('user.home')->with([
				'status'  => 'success',
				'message' => __('locale.payment_gateways.payment_successfully_made'),
			]);
		}

		$user->email_verified_at = Carbon::now();
		$user->save();
		$callback_data = $this->subscriptions->payRegisterPayment($plan, $data, $user);

		if (isset($callback_data->getData()->status)) {

			if ($callback_data->getData()->status == 'success') {
				if ($data['payment_methods'] == PaymentMethods::TYPE_BRAINTREE) {
					return view('auth.payment.braintree', [
						'token'    => $callback_data->getData()->token,
						'post_url' => route('user.registers.braintree', $plan->uid),
					]);
				}

				if ($data['payment_methods'] == PaymentMethods::TYPE_STRIPE) {
					return view('auth.payment.stripe', [
						'session_id'      => $callback_data->getData()->session_id,
						'publishable_key' => $callback_data->getData()->publishable_key,
					]);
				}

				if ($data['payment_methods'] == PaymentMethods::TYPE_AUTHORIZE_NET) {

					$months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];

					return view('auth.payment.authorize_net', [
						'months'   => $months,
						'post_url' => route('user.registers.authorize_net', ['user' => $user->uid, 'plan' => $plan->uid]),
					]);
				}

				if ($data['payment_methods'] == PaymentMethods::TYPE_CASH) {
					return view('auth.payment.offline', [
						'data' => $callback_data->getData()->data,
						'user' => $user->uid,
						'plan' => $plan->uid,
					]);
				}

				if ($request->input('payment_methods') == PaymentMethods::TYPE_EASYPAY) {
					return view('auth.payment.easypay', [
						'request_type' => 'subscription',
						'data'         => $callback_data->getData()->data,
						'user'         => $user->uid,
						'post_data'    => $plan->uid,
					]);
				}

				if ($request->input('payment_methods') == PaymentMethods::TYPE_FEDAPAY) {
					return view('auth.payment.fedapay', [
						'public_key' => $callback_data->getData()->public_key,
						'amount'     => round($plan->price),
						'first_name' => $request->input('first_name'),
						'last_name'  => $request->input('last_name'),
						'email'      => $request->input('email'),
						'item_name'  => __('locale.subscription.payment_for_plan') . ' ' . $plan->name,
						'postData'   => [
							'user_id'      => $user->user_id,
							'request_type' => 'subscription',
							'post_data'    => $plan->uid,
						],
					]);
				}

				if ($data['payment_methods'] == PaymentMethods::TYPE_VODACOMMPESA) {
					return view('auth.payment.vodacom_mpesa', [
						'post_url' => route('user.registers.vodacommpesa', ['user' => $user->uid, 'plan' => $plan->uid]),
					]);
				}


				return redirect()->to($callback_data->getData()->redirect_url);
			}

			$user->delete();

			return redirect()->route('referral.register')->with([
				'status'  => 'error',
				'message' => $callback_data->getData()->message,
			]);
		}

		$user->delete();

		return redirect()->route('referral.register')->with([
			'status'  => 'error',
			'message' => __('locale.exceptions.something_went_wrong'),
		]);
	}
}
