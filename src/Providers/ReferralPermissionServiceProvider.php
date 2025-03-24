<?php

namespace Tonkra\Referral\Providers;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Tonkra\Referral\Helpers\Helper;
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
