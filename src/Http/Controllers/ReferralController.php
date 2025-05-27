<?php

namespace Tonkra\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use Tonkra\Referral\Facades\ReferralSettings;
use Tonkra\Referral\Helpers\Helper;
use Tonkra\Referral\Http\Requests\StoreAdminReferralSetiingsRequest;
use Tonkra\Referral\Library\Tool;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Models\ReferralBonus;
use Tonkra\Referral\Models\ReferralRedemption;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Models\UserPreference;
use Tonkra\Referral\Repositories\Contracts\ReferralCustomerRepository;
use Tonkra\Referral\Rules\Phone;
use Tonkra\Referral\Services\ReferralPaymentService;

class ReferralController extends Controller
{
	protected bool $isReferralEnabled;
	protected ReferralCustomerRepository $customers;
	protected ReferralPaymentService $referralPaymentService;

	public function __construct(ReferralCustomerRepository $customers, ReferralPaymentService $referralPaymentService)
	{
		$this->isReferralEnabled = (bool)ReferralSettings::status();
		$this->customers = $customers;
		$this->referralPaymentService = $referralPaymentService;
	}

	/**
	 * @return Factory|View|Application|RedirectResponse
	 * @throws AuthorizationException
	 */

	public function index(): Factory|View|Application|RedirectResponse
	{
		if (!$this->isReferralEnabled && !Auth::user()->isAdmin() && Auth::user()->active_portal !== 'admin') {
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
		$isAdmin = $user->isAdmin() && $user->active_portal == 'admin';
		$referralStats = $user->referralBonusStats();
		$redemptionStats = $isAdmin ? $user->adminReferralRedemptionStats() : $user->referralBonusRedemptionStats();
		$isReferralEnabled = $this->isReferralEnabled;
		$referrer = $user->referrer;
		$referrer_downline_count = number_format((int) ($referrer?->downliners()->count()), 0, '.', ',');
		$referral_preference = $user->preferences?->getPreference(UserPreference::KEY_REFERRAL);
		$earnings = $user->referralBonuses;
		return $isAdmin ?
			view('referral::admin.referrals.index', compact('breadcrumbs', 'user', 'referrer', 'referrer_downline_count', 'referral_preference', 'referralStats', 'redemptionStats')) :
			view('referral::customer.referrals.index', compact('breadcrumbs', 'user', 'referrer', 'referrer_downline_count', 'referral_preference', 'referralStats', 'redemptionStats', 'earnings'));
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
			Referral::REFERRAL_REDEMPTION_RATE => $request->redemption_rate,
			Referral::REFERRAL_MIN_SMS_REDEMPTION_STATUS => (bool) $request->min_sms_redemption_status,
			Referral::REFERRAL_MIN_SMS_REDEMPTION_AMOUNT => $request->min_sms_redemption_amount,
			Referral::REFERRAL_MIN_WITHDRAWAL_REDEMPTION_STATUS => (bool) $request->min_withdrawal_redemption_status,
			Referral::REFERRAL_MIN_WITHDRAWAL_REDEMPTION_AMOUNT => $request->min_withdrawal_redemption_amount,
			Referral::REFERRAL_MIN_TRANSFER_REDEMPTION_STATUS => (bool) $request->min_transfer_redemption_status,
			Referral::REFERRAL_MIN_TRANSFER_REDEMPTION_AMOUNT => $request->min_transfer_redemption_amount,
			Referral::REFERRAL_GUIDELINES => Helper::sanitizeForEnv($request->guideline),
		];

		Helper::setEnv($data);

		if ($oldStatus !== (bool) $request->status) {
			(bool) $request->status ?
				Helper::addPermissions($permission_list) :
				Helper::removePermissions($permission_list);
		}


		return redirect()->route('referral.index')->with([
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
	#[NoReturn]
	public function downliners(Request $request, ?ReferralUser $user = null): void
	{

		$columns = [
			0 => 'responsive_id',
			1 => 'uid',
			2 => 'uid',
			3 => 'name',
			4 => 'earned_bonus',
			5 => 'balance',
			6 => 'phone',
			7 => 'status',
			8 => 'action',
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
				$nestedData['earned_bonus']         = (int)$user->totalEarnedBonusFromUser($downliner);
				$nestedData['name']          = $downliner->displayName();
				$nestedData['id']               = $downliner->uid;
				$nestedData['created_at']    = __('referral::locale.labels.joined') . ': ' . Tool::formatDate($downliner->created_at);

				$nestedData['status']              = $status;
				$nestedData['status_color']       = $status_color;
				$nestedData['status_label']             = $status_label;

				$nestedData['balance']              = number_format($downliner->sms_unit);
				$nestedData['phone']             		= '<a href="tel:' . $downliner->user->customer->phone . '">' . $downliner->user->customer->phone . '</a>';
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
	 * view all downliners
	 *
	 * @param  Request  $request
	 *
	 * @return void
	 * @throws AuthorizationException
	 */
	#[NoReturn]
	public function redemptions(Request $request, ?ReferralUser $user = null): void
	{

		$columns = [
			0 => 'responsive_id',
			1 => 'uid',
			2 => 'request_id',
			3 => 'downliner',
			4 => 'amount',
			5 => 'payout_method',
			6 => 'status',
			7 => 'processed_at',
			// 8 => 'actions',
		];
		$user = (!$user) ? ReferralUser::find(auth()->id()) : $user;

		$totalData = $user->referralRedemptions()->count();

		$totalFiltered = $totalData;

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir   = $request->input('order.0.dir');

		$query = $user->referralRedemptions()
			->selectRaw('
                MIN(user_id) as user_id,
                request_id,
                SUM(amount) as amount,
                MIN(status) as status,
                payout_method,
                MIN(created_at) as created_at,
                MIN(processed_at) as processed_at
            ')
			->with('user')
			->groupBy('request_id')
			->orderBy($order, $dir);

		$recordsTotal = $query->get()->count();

		if ($search = $request->input('search.value')) {
			$query->whereLike(['user_id', 'request_id', 'amount', 'status', 'payout_method', 'created_at', 'processed_at'], $search);
			$totalFiltered = $query->whereLike(['user_id', 'request_id', 'amount', 'status', 'payout_method', 'created_at', 'processed_at'], $search)
				->groupBy('request_id')
				->get()
				->count();
		} else {
			$totalFiltered = $recordsTotal;
		}

		$redemptions = $query->orderBy($order, $dir)
			->offset($start)
			->limit($limit)
			->get();

		$data = $redemptions->map(function ($redemption) {
			// $topup              = __('referral::locale.buttons.top_up');
			// $report                = __('referral::locale.buttons.report');
			// $copy                    = __('referral::locale.buttons.copy_referral_code');

			if ($redemption->status === ReferralRedemption::STATUS_PENDING) {
				$status = '<span class="badge badge-sm bg-secondary">' . __('referral::locale.referral_redemptions.pending') . '</span>';
			} elseif ($redemption->status === ReferralRedemption::STATUS_PROCESSING) {
				$status = '<span class="badge badge-sm bg-warning">' . __('referral::locale.referral_redemptions.processing') . '</span>';
			} elseif ($redemption->status === ReferralRedemption::STATUS_COMPLETED) {
				$status = '<span class="badge badge-sm bg-success">' . __('referral::locale.referral_redemptions.completed') . '</span>';
			} elseif ($redemption->status === ReferralRedemption::STATUS_FAILED) {
				$status = '<span class="badge badge-sm bg-danger">' . __('referral::locale.referral_redemptions.failed') . '</span>';
			} else {
				$status = '<span class="badge badge-sm bg-dark">' . $redemption->status . '</span>';
			}

			$super_user = true;
			if ($redemption->user->user->id != 1) {
				$super_user = false;
			}

			return [
				'responsive_id'		 => '',
				'uid'          		 => $redemption->uid,
				'avatar'       		 => route('referral.user.user_avatar', $redemption->user->user->uid),
				'amount'       		 => number_format($redemption->amount),
				'payout_method'     => strtoupper(str_replace('_', ' ', __('referral::locale.referral_redemptions.' . $redemption->payout_method))),
				'name'    						=> '',
				'request_id'           		 => $redemption->request_id,
				'processed_at'   		 => $redemption->payout_method == ReferralRedemption::PAYOUT_WALLET
					? ($redemption->status == ReferralRedemption::STATUS_COMPLETED
						? __('referral::locale.labels.redeemed') . ': ' . Tool::customerDateTime($redemption->processed_at)
						: __('referral::locale.labels.created_at') . ': ' . Tool::customerDateTime($redemption->created_at))
					: __('referral::locale.labels.redeemed') . ': ' . Tool::customerDateTime($redemption->processed_at),

				'status'            => $status,
				'super_user'        => $super_user,
				'is_admin'          => ReferralUser::find(auth()->id())->isAdmin(),
				'url'               => route('admin.customers.show', ['customer' => $redemption->user->user->uid]),

			];
		});

		echo json_encode([
			"draw"            => intval($request->input('draw')),
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $totalFiltered,
			"data"            => $data,
		]);

		exit();
	}



	/**
	 * @param Request $request
	 *
	 * @return void
	 */
	#[NoReturn] 
	public function searchAdminRedemptions(Request $request): void
	{
		$columns = [
			0  => 'responsive_id',
			1  => 'uid',
			// 2  => 'request_id',
			2  => 'recipient',
			3  => 'amount',
			4  => 'moneytary_value',
			5  => 'payout_method',
			6  => 'status',
			7 => 'created_at',
			8 => 'actions',
		];

		$totalData = ReferralRedemption::count();

		$totalFiltered = $totalData;

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir   = $request->input('order.0.dir');

		$query = ReferralRedemption::query()
			->selectRaw('
                MIN(user_id) as user_id,
                request_id,
                SUM(amount) as amount,
                MIN(status) as status,
                payout_method,
                MIN(created_at) as created_at
            ')
			->with('user')
			->groupBy('request_id')
			->orderBy($order, $dir);

		$recordsTotal = $query->get()->count();

		if ($search = $request->input('search.value')) {
			$query->whereLike(['request_id', 'amount', 'status', 'payout_method', 'failure_reason'], $search);
			$totalFiltered = $query->whereLike(['request_id', 'amount', 'status', 'payout_method', 'failure_reason'], $search)
				->groupBy('request_id')
				->get()
				->count();
		} else {
			$totalFiltered = $recordsTotal;
		}

		$redemptions = $query->orderBy($order, $dir)
			->offset($start)
			->limit($limit)
			->get();

		$data = $redemptions->map(function ($redemption) {
			$statuses = [
				ReferralRedemption::STATUS_PENDING => ['color' => 'secondary', 'icon' => 'fa fa-clock'],
				ReferralRedemption::STATUS_PROCESSING => ['color' => 'warning', 'icon' => 'fa fa-spinner fa-spin'],
				ReferralRedemption::STATUS_COMPLETED => ['color' => 'success', 'icon' => 'fa fa-thumbs-up'],
				ReferralRedemption::STATUS_FAILED => ['color' => 'danger', 'icon' => 'fa fa-thumbs-down']
			];
			$request_id = '<a href="' . route('referral.admin.redemptions.show', $redemption->request_id) . '" class="text-primary fw-bold">#' . $redemption->request_id . '</a>';
			$status_dropdown_list = ($redemption->status == ReferralRedemption::STATUS_PENDING)
				? [ReferralRedemption::STATUS_PROCESSING, ReferralRedemption::STATUS_COMPLETED, ReferralRedemption::STATUS_FAILED]
				: ($redemption->status == ReferralRedemption::STATUS_PROCESSING
					? [ReferralRedemption::STATUS_COMPLETED, ReferralRedemption::STATUS_FAILED]
					: ($redemption->status == ReferralRedemption::STATUS_FAILED
						? [ReferralRedemption::STATUS_COMPLETED]
						: []));
			return [
				'responsive_id' => '',
				'uid' => $redemption->request_id,
				'user_id' => '<a href="' . route('admin.customers.show', $redemption->user->uid) . '" class="text-primary mr-1">' . $redemption->user->displayName() . '</a>',
				'isAdmin' => $redemption->user->isAdmin(),
				'request_id' => '<div class="d-flex justify-content-left align-items-center">
															<div class="d-flex flex-column">
																	<span class="emp_name text-truncate fw-bold"> ' . $request_id . '</span>
																	<small class="emp_post text-truncate text-muted"> ' . Tool::customerDateTime($redemption->created_at) . '</small>
															</div>
													</div>',
				'avatar' => route('admin.customers.avatar', $redemption->user->uid),
				'name' => $redemption->user->displayName(),
				'created_at' => Tool::customerDateTime($redemption->created_at),
				'actual_payout_method' => $redemption->payout_method,
				'payout_method' => strtoupper(str_replace('_', ' ', $redemption->payout_method)),
				'amount' => number_format((int)$redemption->amount),
				'moneytary_value' => $redemption->payout_details['moneytary_value'] ?? number_format($redemption->amount * ReferralSettings::redeemRate(), 2),
				'status_dropdown_list' => $status_dropdown_list,
				'actual_status' => $redemption->status,
				'status' => '<span class="badge badge-sm bg-' . ($statuses[$redemption->status]['color'] ?? 'dark') . '"><i class="' . $statuses[$redemption->status]['icon'] . '"></i><b class="d-none">' . __('referral::locale.referral_redemptions.' . strtolower($redemption->status), [], $redemption->status) . '</b></span>',
				'edit' => route('referral.admin.redemptions.show', $redemption->request_id)
			];
		});

		echo json_encode([
			"draw"            => intval($request->input('draw')),
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $totalFiltered,
			"data"            => $data,
		]);

		exit();
	}


	/**
	 * @param Request $request
	 *
	 * @return void
	 */
	#[NoReturn]
	public function earnings(Request $request): void
	{
		$columns = [
			0  => 'responsive_id',
			1  => 'uid',
			2  => 'from',
			3  => 'bonus',
			4  => 'type',
			5  => 'status',
			6  => 'created_at',
			// 7 => 'created_at',
			// 8 => 'actions',
		];

		$totalData = ReferralBonus::count();

		$totalFiltered = $totalData;

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')];
		$dir   = $request->input('order.0.dir');

		$query = ReferralBonus::select('uid', 'transaction_id', 'from', 'to', 'bonus', 'original_amount', 'status', 'paid_at', 'created_at')
			->with(['fromUser', 'toUser'])
			->orderBy($order, $dir);

		$recordsTotal = $query->get()->count();

		if ($search = $request->input('search.value')) {
			$query = ReferralBonus::whereLike(['uid', 'transaction_id', 'from', 'to', 'bonus', 'original_amount', 'status', 'paid_at', 'created_at'], $search)
				->orWhereLike(['transaction.type', $search]);
			$totalFiltered = $query->get()->count();
		} else {
			$totalFiltered = $recordsTotal;
		}

		$earnings = $query->orderBy($order, $dir)
			->offset($start)
			->limit($limit)
			->get();

		$data = $earnings->map(function ($earning) {
			$statuses = [
				ReferralBonus::STATUS_PENDING => ['color' => 'secondary', 'icon' => 'fa fa-clock'],
				ReferralBonus::STATUS_PAID => ['color' => 'info', 'icon' => 'fa fa-thumbs-up'],
				ReferralBonus::STATUS_PARTLY_REDEEMED => ['color' => 'warning', 'icon' => 'fa fa-star-half-stroke'],
				ReferralBonus::STATUS_REDEEMED => ['color' => 'success', 'icon' => 'fa fa-star'],
				ReferralBonus::STATUS_REJECTED => ['color' => 'danger', 'icon' => 'fa fa-thumbs-down'],
			];
			
			$is_partially_redeemed = ($earning->status == ReferralBonus::STATUS_PARTLY_REDEEMED && $earning->original_amount != null);
			$remaining = $is_partially_redeemed ? (int) $earning->bonus : '';
			
			return [
				'responsive_id' => '',
				'uid' => $earning->uid,
				'from' => $earning->fromUser->displayName(),
				'isAdmin' => $earning->fromUser->isAdmin(),
				'avatar' => route('referral.user.user_avatar', $earning->fromUser->uid),
				'bonus' => $is_partially_redeemed ? (int) $earning->original_amount : (int) $earning->bonus,
				'created_at' => Tool::customerDateTime($earning->created_at),
				'is_partially_redeemed' => $is_partially_redeemed,
				'original_amount' => (int) $earning->original_amount,
				'remaining' => $is_partially_redeemed ? __('referral::locale.referral_bonuses.amount_left_notice', ['amount' => $remaining]) : '',
				'type' => __('referral::locale.referral_bonuses.' . $earning->transaction->type),
				'actual_status' => $earning->status,
				'status' => '<span class="badge badge-sm bg-' . ($statuses[$earning->status]['color'] ?? 'dark') . '" title="'. __('referral::locale.referral_bonuses.' . strtolower($earning->status), [], $earning->status) .'"><i class="' . $statuses[$earning->status]['icon'] . '"></i>&nbsp;<b class="d-none d-xl-inline-block">' . __('referral::locale.referral_bonuses.' . strtolower($earning->status), [], $earning->status) . '</b></span>',
			];
		});

		echo json_encode([
			"draw"            => intval($request->input('draw')),
			"recordsTotal"    => $recordsTotal,
			"recordsFiltered" => $totalFiltered,
			"data"            => $data,
		]);

		exit();
	}

	/**
	 * @param string $redemption
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */

	public function updateRedemptionStatus(string $redemption, Request $request): JsonResponse
	{
		if (config('app.stage') == 'demo') {
			return response()->json([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		$redemptions = ReferralRedemption::where('request_id', $redemption)->get();

		if (!$redemptions) {
			return response()->json([
				'status'  => 'error',
				'message' => __('referral.locale.referral_redemption.redemption_not_available'),
			]);
		}

		$validated  = $request->validate([
			'status'           => ['required', 'string', Rule::in([ReferralRedemption::STATUS_PENDING, ReferralRedemption::STATUS_PROCESSING, ReferralRedemption::STATUS_COMPLETED, ReferralRedemption::STATUS_FAILED])],
		]);

		$result = $this->referralPaymentService->updateRedemptionStatus($redemptions, $validated);

		return response()->json([
			'status'  => 'success',
			'message' => __('referral.locale.referral_redemption.redemption_successfully_updated'),
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
    #[NoReturn] 
    public function searchAdminReferrals(Request $request): void
    {
        // Verify admin access
        if (!auth()->user()->isAdmin() || auth()->user()->active_portal !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
    
        $columns = [
            0 => 'responsive_id',   // not sortable
            1 => 'uid',
            2 => 'name',            // user.first_name + user.last_name (sort by first_name)
            3 => 'upliner',         // referrer.user.first_name (sort by referrer_user.first_name)
            4 => 'downliner_count', // downliners_count
            5 => 'available_bonus', // available_bonus
            6 => 'balance',         // user.sms_unit
            7 => 'status',
            8 => 'created_at',
            9 => 'action',          // not sortable
        ];
        
        $hasSearch = $request->filled('search');
        $search = $request->input('search.value');
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderIndex = (int) $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        
        $orderColumn = $columns[$orderIndex] ?? 'created_at';
        
        $query = ReferralUser::with([
                'referrer.user:id,uid,first_name,last_name',
                'downliners',
            ])
            ->withCount(['downliners as downliners_count'])
            ->withSum(['paidReferralBonuses as available_bonus'], 'bonus')
            ->leftJoin('referrals', 'users.id', '=', 'referrals.user_id')
            ->leftJoin('users as referrer_users', 'referrals.referred_by', '=', 'referrer_users.id')
            ->select('users.*');
        
        $totalData = $query->count();
        $totalFiltered = $totalData;
        
        // Fix the order column to use the correct table
        $orderableColumns = [
            'uid' => 'users.uid',
            'name' => 'users.first_name',
            'upliner' => 'referrer_users.first_name',
            'downliner_count' => 'downliners_count',
            'available_bonus' => 'available_bonus',
            'balance' => 'users.sms_unit',
            'status' => 'users.status',
            'created_at' => 'users.created_at',
        ];
        
        if($hasSearch){
             $query->where(function($q) use ($search, $orderableColumns) {
                $q->where('users.uid', 'like', "%{$search}%")
                  ->orWhere('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                  })
                  ->orWhere($orderableColumns['balance'], 'like', "%{$search}%")
                  ->orWhere($orderableColumns['status'], 'like', "%{$search}%")
                  
                  // Search in referrer fields
                  ->orWhereHas('referrer.user', function($q) use ($search) {
                      $q->where('uid', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                  })
                  
                  // Search in upliner name (from joined table)
                  ->orWhere($orderableColumns['upliner'], 'like', "%{$search}%")
                  
                  // Search downliners_count (using having clause)
                  ->orHaving('downliners_count', 'like', "%{$search}%")
                  
                  // Search available_bonus (using having clause)
                  ->orHaving('available_bonus', 'like', "%{$search}%");
                });
            
            $totalFiltered = $query->count();
        }
        
        $users = $query->offset($start)
                        ->limit($limit)
                        ->orderBy($orderableColumns[$orderColumn], $orderDir)
                        ->get();
    
        $data = [];
        foreach ($users as $user) {
            $hasReferrer = $user->referrer !== null;
            $referrer = $hasReferrer ? $user->referrer->user : null;
            $isSuperUser = $user->id == 1;
            
            $data[] = [
                'responsive_id' => '',
                'uid' => $user->uid,
                'avatar' => route('referral.user.user_avatar', $user->uid),
                'earned_bonus' => __('referral::locale.referral_bonuses.total_amount_notice', [
                    'amount' => (int)$user->totalEarnedBonus()
                ]),
                'available_bonus' => number_format($user->paidReferralBonuses()->sum('bonus')),
                'name' => $user->displayName(),
                'user_id' => '<a href="'.route('admin.customers.show', $user->user->uid).'" class="text-primary mr-1">'.$user->user->displayName().'</a>',
                'id' => $user->uid,
                'created_at' => __('referral::locale.labels.joined').': '.Tool::formatDate($user->created_at),
                'hasReferrer' => $hasReferrer,
                'referrer' => $hasReferrer 
                    ? ($referrer->isAdmin()
                        ? $user->referrer->displayName()
                        : '<a href="'.route('admin.customers.show', $referrer->uid).'" class="text-primary mr-1">'.$referrer->displayName().'</a>')
                    : null,
                'referrer_avatar' => $hasReferrer ? route('referral.user.user_avatar', $referrer->uid) : null,
                'downliner_count' => $user->downliners->count(),
                'status' => $user->status ? 'toggle-right' : 'toggle-left',
                'status_color' => $user->status ? 'text-success' : 'text-danger',
                'status_label' => $user->status 
                    ? __('referral::locale.labels.active') 
                    : __('referral::locale.labels.inactive'),
                'balance' => number_format($user->sms_unit),
                'copy' => $user->referralCode(),
                'copy_label' => __('referral::locale.buttons.copy_referral_code'),
                'report' => $user->uid,
                'report_label' => __('referral::locale.buttons.report'),
                'top_up' => $user->uid,
                'top_up_label' => __('referral::locale.buttons.top_up'),
                'super_user' => $isSuperUser,
                'is_admin' => true,
                'url' => route('admin.customers.show', $user->uid)
            ];
        }
    
        echo json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ]);
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
		$referrer = $user->referrer;
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

	public function redeemBonus(Request $request)
	{
		if (config('app.stage') == 'demo') {
			return redirect()->route('referral.admin.settings')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		if (!ReferralSettings::minSmsRedeemStatus()) {
			return redirect()->back()->with([
				'status'  => 'error',
				'message' => 'Sorry! SMS redemption is not available at the moment',
			]);
		}

		$user = ReferralUser::find(Auth::user()->id);
		$availableBonusAmount = $user->paidReferralBonuses()->sum('bonus');

		$validated  = $request->validate([
			'amount' => 'required|numeric|min:' . ReferralSettings::minSmsRedeemAmount() . '|max:' . $availableBonusAmount,
		], [
			'amount.required' => 'Please enter an amount',
			'amount.numeric' => 'Amount must be a number',
			'amount.min' => 'Amount must be at least :min'
		]);

		$result = $this->referralPaymentService->redeemBonuses($user, $validated['amount'], ReferralRedemption::PAYOUT_SMS);

		return redirect()->route('referral.index')->with([
			'status'  => 'success',
			'message' => __('referral::locale.referral_bonuses.bonus_successfully_redeemed', ['bonus' => $validated['amount'], 'type' => 'redeemed']),
		]);
	}

	public function withdrawBonus(Request $request)
	{
		if (config('app.stage') == 'demo') {
			return redirect()->route('referral.admin.settings')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		if (!ReferralSettings::minWithdrawalRedeemStatus()) {
			return redirect()->back()->with([
				'status'  => 'error',
				'message' => 'Sorry! withdrawal redemption is not available at the moment',
			]);
		}

		$user = ReferralUser::find(Auth::user()->id);
		$availableBonusAmount = $user->paidReferralBonuses()->sum('bonus');

		$validated  = $request->validate([
			'amount' => 'required|numeric|min:' . ReferralSettings::minWithdrawalRedeemAmount() . '|max:' . $availableBonusAmount,
			'network' => ['required', Rule::in(ReferralRedemption::PAYOUT_NETWORK_MTN, ReferralRedemption::PAYOUT_NETWORK_TELECEL, ReferralRedemption::PAYOUT_NETWORK_AIRTELTIGO)],
			'accountNumber' => ['required', new Phone($request->accountNumber)],
			'accountName' => ['required', 'string', 'min:3', 'max:255'],
		]);

		$payoutDetails = [
			'network' 						=> $validated['network'],
			'accountNumber' 			=> $validated['accountNumber'],
			'accountName' 				=> $validated['accountName'],
		];

		$result = $this->referralPaymentService->redeemBonuses($user, $validated['amount'], ReferralRedemption::PAYOUT_WALLET, $payoutDetails);

		return redirect()->route('referral.index')->with([
			'status'  => 'success',
			'message' => __('referral::locale.referral_bonuses.bonus_successfully_redeemed', ['bonus' => $validated['amount'] . '(' . $result['moneytary_value'] . ')', 'type' => 'withdrawn']),
		]);
	}

	public function transferBonus(Request $request)
	{
		if (config('app.stage') == 'demo') {
			return redirect()->route('referral.admin.settings')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		if (!ReferralSettings::minTransferRedeemStatus()) {
			return redirect()->back()->with([
				'status'  => 'error',
				'message' => 'Sorry! Transfer redemption is not available at the moment',
			]);
		}

		$user = ReferralUser::find(Auth::user()->id);
		$availableBonusAmount = $user->paidReferralBonuses()->sum('bonus');

		$validated  = $request->validate([
			'recipient' => [
				'required',
				'exists:referrals,referral_code',
				function ($attribute, $value, $fail) use ($user) {
					if ($value === $user->referralCode()) {
						$fail('You cannot transfer to your own account.');
					}
				},
			],
			'amount' => ['required', 'numeric', 'min:' . ReferralSettings::minTransferRedeemAmount(), 'max:' . $availableBonusAmount],
		]);

		$payoutDetails = [
			'recipient' 				=> $validated['recipient'],
		];

		$result = $this->referralPaymentService->redeemBonuses($user, $validated['amount'], ReferralRedemption::PAYOUT_TRANSFER, $payoutDetails);

		return redirect()->route('referral.index')->with([
			'status'  => 'success',
			'message' => __('referral::locale.referral_bonuses.bonus_successfully_redeemed', ['bonus' => $validated['amount'], 'type' => 'transfered']),
		]);
	}

	public function BulkTransferBonus(Request $request) : JsonResponse
	{
		if (config('app.stage') == 'demo') {
			return redirect()->route('referral.admin.settings')->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}

		if (!ReferralSettings::minTransferRedeemStatus()) {
			return redirect()->back()->with([
				'status'  => 'error',
				'message' => 'Sorry! Transfer redemption is not available at the moment',
			]);
		}
		

		$user = ReferralUser::find(Auth::user()->id);
		$availableBonusAmount = $user->paidReferralBonuses()->sum('bonus');
		$minTransferAmount = ReferralSettings::minTransferRedeemAmount();
		$numberOfRecipients = count($request->input('ids', []));
		
		$validated  = $request->validate([
			'ids'    => 'required|array|min:1',
            'ids.*'  => 'required|string|exists:users,uid',
			'amount' => [
                'bail', // Stop after first failure
                'required',
                'numeric',
                'min:'. $minTransferAmount,
                function ($attribute, $value, $fail) use ($request, $availableBonusAmount, $numberOfRecipients) {
                    $totalMin = $value * $numberOfRecipients;

                    if ($availableBonusAmount < $totalMin) {
                        $fail(sprintf(
                            "Minimum amount required: %s (%s × %d recipients)",
                            Tool::format_number($totalMin),
                            Tool::format_number($value),
                            $numberOfRecipients
                        ));
                    }
                },
                'max:' . $availableBonusAmount
            ],
		]);
		
		$totalMin = $validated['amount'] * $numberOfRecipients;
		$recipients = ReferralUser::whereIn('uid', $validated['ids'])->get();
		
		foreach($recipients as $recipient){
    		$payoutDetails = [
    			'recipient' => $recipient->referralCode(),
    		];
    
    		$result = $this->referralPaymentService->redeemBonuses($user, $validated['amount'], ReferralRedemption::PAYOUT_TRANSFER, $payoutDetails);
		    
		}


		return response()->json([
			'status'  => 'success',
			'message' => __('referral::locale.referral_bonuses.bonus_successfully_redeemed', ['bonus' => Tool::format_number($totalMin), 'type' => 'transfered']),
		]);
	}
}
