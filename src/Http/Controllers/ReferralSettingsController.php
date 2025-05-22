<?php

namespace Tonkra\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tonkra\Referral\Http\Requests\StoreReferralSettingsRequest;
use Tonkra\Referral\Repositories\Contracts\ReferralSettingsRepository;

class ReferralSettingsController extends Controller
{

	protected ReferralSettingsRepository $settings;

	/**
	 * SettingsController constructor.
	 *
	 * @param ReferralSettingsRepository $settings
	 */
	public function __construct(ReferralSettingsRepository $settings)
	{
		$this->settings = $settings;
	}
	/**
	 * Save referral settings.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function saveReferralSettings(StoreReferralSettingsRequest $request): RedirectResponse
	{

		if (config('app.stage') == 'demo') {
			return redirect()->route('admin.settings.general')->withInput(['tab' => 'referral'])->with([
				'status'  => 'error',
				'message' => 'Sorry! This option is not available in demo mode',
			]);
		}


		$this->settings->saveReferralSettings($request->except('_token'));

		return redirect()->route('admin.settings.general')->withInput(['tab' => 'referral'])->with([
			'status'  => 'success',
			'message' => __('referral::locale.settings.settings_successfully_updated'),
		]);
	}
}
