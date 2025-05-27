<?php

namespace Tonkra\Referral\Providers;

use App\Helpers\Helper as AppHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Tonkra\Referral\Helpers\Helper;

class ReferralMenuServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 */
	public function register()
	{

		$localeData["Referrals"] = "Referrals";

		$localeData["Referral Settings"] = "Referral Settings";


		Helper::updatePhpLocale('en', 'menu', $localeData);
	}

	/**
	 * Bootstrap services.
	 */
	public function boot()
	{
		View::composer('*', function ($view) {
			$this->extendMenu();
		});
	}

	/**
	 * Extend the menu with the referral system menu item.
	 */
	protected function extendMenu()
	{
		// Get existing menu data
		$menuData = AppHelper::menuData();

		if (auth()->user() && auth()->user()->active_portal == 'admin') {
			$adminReferralMenuItem = [
				'url'    => route('referral.index'),
				'slug'   => "referrals",
				'name'   => "Referrals",
				'i18n'   => "Referrals",
				'icon'   => "heart",
				// 'classlist' => (bool)config('referral.status') ? '' : 'hidden',
				'access' => "view_referral",
			];

			// Find the index of the "Invoices" menu
			$invoicesIndex = null;
			foreach ($menuData['admin'] as $index => &$adminMenu) {
				if ($adminMenu['name'] === 'Invoices') {
					$invoicesIndex = $index;
				}
			}

			// If "Invoices" is found, insert the new item after it
			if ($invoicesIndex !== null) {
				array_splice($menuData['admin'], $invoicesIndex + 1, 0, [$adminReferralMenuItem]);
			}
		}
		
		
		// Define the referral menu item
		$referralMenuItem = [
			'url'    => route('referral.index'),
			'slug'   => "referrals",
			'name'   => "Referrals",
			'i18n'   => "Referrals",
			'icon'   => "heart",
			'classlist' => (bool)config('referral.status') ? '' : 'hidden',
			'access' => "view_referral",
		];
		
		// Find the index of the "Report" menu
		$reportsIndex = null;
		foreach ($menuData['customer'] as $index => &$customerMenu) {
			if (strtolower($customerMenu['name']) === 'reports') {
				$reportsIndex = $index;
				break;
			}
		}

		// If "reports" is found, insert the new item after it
		if ($reportsIndex !== null) {
			array_splice($menuData['customer'], $reportsIndex + 1, 0, [$referralMenuItem]);
		}


		// Append the referral menu item
		$menuData[] = $referralMenuItem;
		$menuData = json_decode(json_encode($menuData));

		// Share the updated menuData with views
		View::share('menuData', [$menuData, $menuData]);
	}
}
