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
		
		if (auth()->user() && auth()->user()->active_portal == 'admin'){
			$referralSettingsMenuItem = [
				'url'    => route('referral.admin.setting'),
				'slug'   => config('app.admin_path') . '/referral',
				'name'   => "Referral Settings",
				'i18n'   => "Referral Settings",
				'icon'   => "heart",
				'classlist' => (bool)config('referral.status') ? '' : 'hidden',
				'access' => "general settings",
			];

			foreach ($menuData['admin'] as &$adminMenu) {
				if ($adminMenu['name'] === 'Settings') {
						$adminMenu['submenu'][] = $referralSettingsMenuItem;
						break;
				}
			}
		}
		// Insert at index 13 (after 'Reports')
		array_splice($menuData['customer'], 13, 0, [$referralMenuItem]);


		// Append the referral menu item
		$menuData[] = $referralMenuItem;
		$menuData = json_decode(json_encode($menuData));

		// Share the updated menuData with views
		View::share('menuData', [$menuData, $menuData]);
	}
}
