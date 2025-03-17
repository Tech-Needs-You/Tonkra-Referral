<?php

namespace Tonkra\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Repositories\Contracts\CustomerRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Models\UserPreference as ModelsUserPreference;

class ReferralsController extends Controller
{
	protected bool $isReferralEnabled;
	protected CustomerRepository $customers;

	public function __construct(CustomerRepository $customers)
	{
		$this->isReferralEnabled = (bool)json_decode(\App\Helpers\Helper::app_config('referral_system'))->status;
		$this->customers = $customers;
	}

	/**
	 * @return Application|Factory|View
	 * @throws AuthorizationException
	 */

	public function index(): Factory|View|Application|RedirectResponse
	{
		if (!$this->isReferralEnabled) {
			return redirect()->route('user.home')->with([
				'status'  => 'error',
				'message' => __('locale.referrals.referral_not_active'),
			]);
		}

		$this->authorize('view_referral');

		$breadcrumbs = [
			['link' => url("dashboard"), 'name' => __('locale.menu.Dashboard')],
			['name' => __('locale.menu.Referrals')],
		];

		$user = ReferralUser::find(auth()->id());
		$isReferralEnabled = (bool)json_decode(\App\Helpers\Helper::app_config('referral_system'))->status;
		$referrer = $user->referrer();
		$referrer_downline_count = number_format($referrer?->downliners()->count(), 0, '.', ',');
		$referral_preference = $user->preferences?->getPreference(ModelsUserPreference::KEY_REFERRAL);

		return view('customer.Referrals.index', compact('breadcrumbs', 'user', 'referrer', 'referrer_downline_count', 'referral_preference'));
	}

	/**
	 * view all downliners
	 *
	 * @param  Request  $request
	 *
	 * @return void
	 * @throws AuthorizationException
	 */
	// #[NoReturn] public function downliners(Request $request, User $user = null): void
	// {

	// 	$columns = [
	// 		0 => 'responsive_id',
	// 		1 => 'uid',
	// 		2 => 'uid',
	// 		3 => 'name',
	// 		4 => 'email',
	// 		5 => 'balance',
	// 		6 => 'status',
	// 		7 => 'action',
	// 	];
	// 	$user = (!$user) ? auth()->user() : $user;

	// 	$totalData = $user->downliners()->count();
	// 	// dd('jhg');

	// 	$totalFiltered = $totalData;

	// 	$limit = $request->input('length');
	// 	$start = $request->input('start');
	// 	$order = $columns[$request->input('order.0.column')];
	// 	$dir   = $request->input('order.0.dir');

	// 	if (empty($request->input('search.value'))) {
	// 		$downliners = $user->downliners()->offset($start)
	// 			->limit($limit)
	// 			->orderBy($order, $dir)
	// 			->get();
	// 	} else {
	// 		$search = $request->input('search.value');

	// 		$downliners = $user->downliners()->whereLike(['uid', 'first_name', 'last_name', 'status', 'email'], $search)
	// 			->offset($start)
	// 			->limit($limit)
	// 			->orderBy($order, $dir)
	// 			->get();

	// 		$totalFiltered = $user->downliners()->whereLike(['uid', 'first_name', 'last_name', 'status', 'email'], $search)->count();
	// 	}

	// 	$data = [];
	// 	if (! empty($downliners)) {
	// 		foreach ($downliners as $downliner) {
	// 			$topup              = __('locale.buttons.top_up');
	// 			$report                = __('locale.buttons.report');
	// 			$copy                    = __('locale.buttons.copy_referral_code');

	// 			if ($downliner->status === true) {
	// 				$status_label = __('locale.labels.active');
	// 				$status_color = 'text-success';
	// 				$status = 'toggle-right';
	// 			} else {
	// 				$status_label = __('locale.labels.inactive');
	// 				$status_color = 'text-danger';
	// 				$status = 'toggle-left';
	// 			}

	// 			$super_user = true;
	// 			if ($downliner->id != 1) {
	// 				$super_user = false;
	// 			}

	// 			$nestedData['responsive_id'] = '';
	// 			$nestedData['uid']           = $downliner->uid;
	// 			$nestedData['avatar']        = route('user.user_avatar', $downliner->uid);
	// 			$nestedData['email']         = '<a class="text-info" href="mailto:' . $downliner->email . '">' . $downliner->email . '</a>';
	// 			$nestedData['name']          = $downliner->displayName();
	// 			$nestedData['id']               = $downliner->uid;
	// 			$nestedData['created_at']    = __('locale.labels.joined') . ': ' . Tool::formatDate($downliner->created_at);

	// 			$nestedData['status']              = $status;
	// 			$nestedData['status_color']       = $status_color;
	// 			$nestedData['status_label']             = $status_label;

	// 			$nestedData['balance']             = $downliner->sms_unit;
	// 			$nestedData['copy']                 = $downliner->uid;
	// 			$nestedData['copy_label']           = $copy;
	// 			$nestedData['report']            = $downliner->uid;
	// 			$nestedData['report_label']      = $report;
	// 			$nestedData['top_up']            = $downliner->uid;
	// 			$nestedData['top_up_label']      = $topup;
	// 			$nestedData['super_user']        = $super_user;
	// 			$nestedData['is_admin']          = Auth::user()->isAdmin();
	// 			$nestedData['url']               = route('admin.customers.show', ['customer' => $downliner->uid]);

	// 			$data[] = $nestedData;
	// 		}
	// 	}

	// 	$json_data = [
	// 		"draw"            => intval($request->input('draw')),
	// 		"recordsTotal"    => $totalData,
	// 		"recordsFiltered" => $totalFiltered,
	// 		"data"            => $data,
	// 	];

	// 	echo json_encode($json_data);
	// 	exit();
	// }

	/**
	 * Save user referral preferences 
	 *
	 * @param  Request  $request
	 * @param  string  $key
	 *
	 * @return RedirectResponse
	 */
	// public function savePreference(Request $request, $key): RedirectResponse
	// {

	// 	if (config('app.stage') == 'demo') {
	// 		return redirect()->route('login')->with([
	// 			'status'  => 'error',
	// 			'message' => 'Sorry! This option is not available in demo mode',
	// 		]);
	// 	}

	// 	$user = ReferralUser::find(auth()->id());
	// 	$preferences = $request->except('_token');
	// 	$preferences['status'] = isset($preferences['status']) ? $preferences['status'] : false;

	// 	$response = $user->savePreference($preferences, $key);

	// 	if ($response->getData()->status == 'success') {
	// 		// Add view_referral to customer's permissions
	// 		$this->customers->addPermissions($user, ['view_referral']);
	// 		return redirect()->to(URL::previous())->with([
	// 			'status'  => 'success',
	// 			'message' => ucfirst(__('locale.preferences.preference_saved_succeessfully', ['key' => $key])),
	// 		]);
	// 	}

	// 	return redirect()->to(URL::previous())->with([
	// 		'status'  => 'error',
	// 		'message' => $response->getData()->message,
	// 	]);
	// }
}
