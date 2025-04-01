<?php

namespace Tonkra\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\Tool;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use Tonkra\Referral\Facades\ReferralSettings;
use Tonkra\Referral\Helpers\Helper;
use Tonkra\Referral\Http\Requests\StoreAdminReferralSetiingsRequest;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Models\UserPreference;
use Tonkra\Referral\Repositories\Contracts\ReferralCustomerRepository;

class ReferralController extends Controller
{
	protected bool $isReferralEnabled;
	protected ReferralCustomerRepository $customers;

	public function __construct(ReferralCustomerRepository $customers)
	{
		$this->isReferralEnabled = (bool)ReferralSettings::status();
		$this->customers = $customers;
	}

	/**
	 * @return Factory|View|Application|RedirectResponse
	 * @throws AuthorizationException
	 */

	public function index(): Factory|View|Application|RedirectResponse
	{
		if (!$this->isReferralEnabled) {
			return redirect()->route('user.home')->with([
				'status'  => 'error',
				'message' => __('referral::locale.referrals.referral_not_active'),
			]);
		}

		$this->authorize(Referral::PERMISSION_VIEW_REFERRAL);

		$breadcrumbs = [
			['link' => url("dashboard"), 'name' => __('referral::locale.menu.Dashboard')],
			['name' => __('referral::locale.menu.Referrals')],
		];

		$user = ReferralUser::find(auth()->id());
		$isReferralEnabled = $this->isReferralEnabled;
		$referrer = $user->referrer();
		$referrer_downline_count = number_format((int) ($referrer?->downliners()->count()), 0, '.', ',');
		$referral_preference = $user->preferences?->getPreference(UserPreference::KEY_REFERRAL);

		return view('referral::customer.referrals.index', compact('breadcrumbs', 'user', 'referrer', 'referrer_downline_count', 'referral_preference'));
	}

	/**
	 * @return Factory|View|Application|RedirectResponse
	 * @throws AuthorizationException
	 */

	public function showAdminReferralSettings(): Factory|View|Application|RedirectResponse
	{
		$this->authorize(Referral::PERMISSION_REFERRAL_SETTINGS);

		$breadcrumbs = [
			['link' => url("dashboard"), 'name' => __('referral::locale.menu.Dashboard')],
			['name' => __('referral::locale.menu.Referral Settings')],
		];

		return view('referral::admin.settings.referral', compact('breadcrumbs'));
	}

	/**
	 * @param StoreAdminReferralSetiingsRequest $request
	 * @return RedirectResponse
	 * @throws AuthorizationException
	 */
	public function storeAdminReferralSettings(StoreAdminReferralSetiingsRequest $request): RedirectResponse
	{

		if (config('app.stage') == 'demo') {
			return redirect()->route('referral.admin.settings')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}
		$oldStatus = ReferralSettings::status();
		$permission_list = [Referral::PERMISSION_VIEW_REFERRAL];

		$data = [
			Referral::REFERRAL_STATUS => (bool) $request->status,
			Referral::REFERRAL_BONUS => $request->bonus,
			Referral::REFERRAL_EMAIL_NOTIFICATION => (bool) $request->email_notification,
			Referral::REFERRAL_SMS_NOTIFICATION => (bool) $request->sms_notification,
			Referral::REFERRAL_DEFAULT_SENDERID => $request->default_senderid,
		];
		Helper::setEnv($data);

		if ($oldStatus !== (bool) $request->status) {
			(bool) $request->status ?
				Helper::addPermissions($permission_list) :
				Helper::removePermissions($permission_list);
		}


		return redirect()->route('referral.admin.setting')->with([
			'status'  => 'success',
			'message' => __('locale.settings.settings_successfully_updated'),
		]);
	}

	/**
	 * view all downliners
	 *
	 * @param  Request  $request
	 *
	 * @return void
	 * @throws AuthorizationException
	 */
	#[NoReturn] public function downliners(Request $request, ?ReferralUser $user = null): void
	{

		$columns = [
			0 => 'responsive_id',
			1 => 'uid',
			2 => 'uid',
			3 => 'name',
			4 => 'email',
			5 => 'balance',
			6 => 'status',
			7 => 'action',
		];
		$user = (!$user) ? ReferralUser::find(auth()->id()) : $user;

		$totalData = $user->downliners()->count();

		$totalFiltered = $totalData;

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir   = $request->input('order.0.dir');

		if (empty($request->input('search.value'))) {
			$downliners = $user->downliners()->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();
		} else {
			$search = $request->input('search.value');

			$downliners = $user->downliners()->whereLike(['uid', 'first_name', 'last_name', 'status', 'email'], $search)
				->offset($start)
				->limit($limit)
				->orderBy($order, $dir)
				->get();

			$totalFiltered = $user->downliners()->whereLike(['uid', 'first_name', 'last_name', 'status', 'email'], $search)->count();
		}

		$data = [];
		if (! empty($downliners)) {
			foreach ($downliners as $downliner) {
				$topup              = __('referral::locale.buttons.top_up');
				$report                = __('referral::locale.buttons.report');
				$copy                    = __('referral::locale.buttons.copy_referral_code');

				if ($downliner->status === true) {
					$status_label = __('referral::locale.labels.active');
					$status_color = 'text-success';
					$status = 'toggle-right';
				} else {
					$status_label = __('referral::locale.labels.inactive');
					$status_color = 'text-danger';
					$status = 'toggle-left';
				}

				$super_user = true;
				if ($downliner->id != 1) {
					$super_user = false;
				}

				$nestedData['responsive_id'] = '';
				$nestedData['uid']           = $downliner->uid;
				$nestedData['avatar']        = route('referral.user.user_avatar', $downliner->uid);
				$nestedData['email']         = '<a class="text-info" href="mailto:' . $downliner->email . '">' . $downliner->email . '</a>';
				$nestedData['name']          = $downliner->displayName();
				$nestedData['id']               = $downliner->uid;
				$nestedData['created_at']    = __('referral::locale.labels.joined') . ': ' . Tool::formatDate($downliner->created_at);

				$nestedData['status']              = $status;
				$nestedData['status_color']       = $status_color;
				$nestedData['status_label']             = $status_label;

				$nestedData['balance']             = $downliner->sms_unit;
				$nestedData['copy']                 = $downliner->referralCode();
				$nestedData['copy_label']           = $copy;
				$nestedData['report']            = $downliner->uid;
				$nestedData['report_label']      = $report;
				$nestedData['top_up']            = $downliner->uid;
				$nestedData['top_up_label']      = $topup;
				$nestedData['super_user']        = $super_user;
				$nestedData['is_admin']          = ReferralUser::find(auth()->id())->isAdmin();
				$nestedData['url']               = route('admin.customers.show', ['customer' => $downliner->uid]);

				$data[] = $nestedData;
			}
		}

		$json_data = [
			"draw"            => intval($request->input('draw')),
			"recordsTotal"    => $totalData,
			"recordsFiltered" => $totalFiltered,
			"data"            => $data,
		];

		echo json_encode($json_data);
		exit();
	}

	/**
	 * Save user referral preferences 
	 *
	 * @param  Request  $request
	 * @param  string  $key
	 *
	 * @return RedirectResponse
	 */
	public function savePreference(Request $request, $key): RedirectResponse
	{

		if (config('app.stage') == 'demo') {
			return redirect()->route('login')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		$user = ReferralUser::find(auth()->id());
		$preferences = $request->except('_token');
		$preferences['status'] = isset($preferences['status']) ? $preferences['status'] : false;

		$response = $user->savePreference($preferences, $key);

		if ($response->getData()->status == 'success') {
			// Add view_referral to customer's permissions
			$this->customers->addPermissions($user, ['view_referral']);
			return redirect()->to(URL::previous())->with([
				'status'  => 'success',
				'message' => ucfirst(__('referral::locale.preferences.preference_saved_succeessfully', ['key' => $key])),
			]);
		}

		return redirect()->to(URL::previous())->with([
			'status'  => 'error',
			'message' => $response->getData()->message,
		]);
	}

	/**
	 * get user avatar
	 *
	 * @return mixed
	 */
	public function user_avatar(ReferralUser $user): mixed
	{
		if (! empty($user->imagePath())) {

			try {
				$image = Image::make($user->imagePath());
			} catch (NotReadableException) {
				$user->image = null;
				$user->save();
				$image = Image::make(public_path('images/profile/profile.jpg'));
			}
		} else {
			$image = Image::make(public_path('images/profile/profile.jpg'));
		}
		return $image->response();
	}
	
	/**
	 * get referrer referrer_avatar
	 */
	public function referrer_avatar(?ReferralUser $user = null): mixed
	{
		$user = (!$user) ? ReferralUser::find(auth()->id()) : $user;
		$referrer = $user->referrer();
		if (! empty($referrer?->imagePath())) {

			try {
				$image = Image::make($referrer->imagePath());
			} catch (NotReadableException) {
				$referrer->image = null;
				$referrer->save();

				$image = Image::make(public_path('images/profile/profile.jpg'));
			}
		} else {
			$image = Image::make(public_path('images/profile/profile.jpg'));
		}

		return $image->response();
	}
}
