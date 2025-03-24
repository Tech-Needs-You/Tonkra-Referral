<?php

namespace Tonkra\Referral\Providers;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Tonkra\Referral\Helpers\Helper;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Models\ReferralRole;

class ReferralPermissionServiceProvider extends ServiceProvider
{
	public function boot()
	{
		// Run only in console mode & after package publishing
		if ($this->app->runningInConsole() && Schema::hasTable('roles')) {
			// $this->assignReferralPermissionToAdminRole();

			$newPermissions = [
				'referral settings' => [
					'display_name' => 'referral',
					'category'     => 'Settings',
				],
			];
			Helper::updateConfigPermission($newPermissions, "permissions");

			$newCustomerPermissions = [
				Referral::PERMISSION_VIEW_REFERRAL => [
					'display_name' => 'read_referral',
					'category'     => 'Referral',
					'default'      => true,
				],
			];
			Helper::updateConfigPermission($newCustomerPermissions, "customer-permissions");

			config('referral.status') ?
				Helper::addPermissions([Referral::PERMISSION_VIEW_REFERRAL]) :
				Helper::removePermissions([Referral::PERMISSION_VIEW_REFERRAL]);
		}
	}

	protected function assignReferralPermissionToAdminRole()
	{
		if ($role = Role::where('name', ReferralRole::ROLE_NAME_ADMIN)->first()) {
			$role->permissions()->firstOrCreate(['name' => 'referral settings'], [
				'display_name' => 'referral',
				'category' => 'Settings',
			]);
		}
	}
}
