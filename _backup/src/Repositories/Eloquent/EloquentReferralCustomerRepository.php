<?php

namespace Tonkra\Referral\Repositories\Eloquent;

use App\Helpers\Helper;
use App\Models\Customer;

use App\Notifications\WelcomeEmailNotification;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use App\Exceptions\GeneralException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Throwable;
use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Repositories\Contracts\ReferralCustomerRepository;
use Tonkra\Referral\Repositories\Eloquent\EloquentReferralBaseRepository;

/**
 * Class EloquentCustomerRepository.
 */
class EloquentReferralCustomerRepository extends EloquentReferralBaseRepository implements ReferralCustomerRepository
{


	/**
	 * @var Repository
	 */
	protected Repository $config;

	/**
	 * EloquentCustomerRepository constructor.
	 *
	 * @param ReferralUser       $user
	 * @param Repository $config
	 */
	public function __construct(ReferralUser $user, Repository $config)
	{
		parent::__construct($user);
		$this->config = $config;
	}

	/**
	 * @param array $input
	 * @param bool  $confirmed
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 *
	 */
	public function store(array $input, bool $confirmed = false): ReferralUser
	{

		/** @var ReferralUser $user */
		$user = $this->make(Arr::only($input, ['first_name', 'last_name', 'email', 'status', 'timezone', 'locale']));

		if (empty($user->locale)) {
			$user->locale = $this->config->get('app.locale');
		}

		if (empty($user->timezone)) {
			$user->timezone = $this->config->get('app.timezone');
		}

		$user->email_verified_at = now();
		$user->is_admin          = false;
		$user->is_customer       = true;
		$user->active_portal     = 'customer';

		if (! $this->save($user, $input)) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		Customer::create([
			'user_id'       => $user->id,
			'phone'         => $input['phone'],
			'permissions'   => Customer::customerPermissions(),
			'notifications' => json_encode([
				'login'        => 'no',
				'sender_id'    => 'yes',
				'keyword'      => 'yes',
				'subscription' => 'yes',
				'promotion'    => 'yes',
				'profile'      => 'yes',
			]),
		]);

		if (isset($input['welcome_message'])) {
			$user->notify(new WelcomeEmailNotification($user->first_name, $user->last_name, $user->email, route('login'), $input['password']));
		}

		return $user;
	}


	/**
	 * @param ReferralUser  $customer
	 * @param array $input
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 */
	public function update(ReferralUser $customer, array $input): ReferralUser
	{

		$customer->fill(Arr::except($input, 'password'));

		if (! $this->save($customer, $input)) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		return $customer;
	}

	/**
	 * @param ReferralUser  $user
	 * @param array $input
	 *
	 * @return bool
	 */
	private function save(ReferralUser $user, array $input): bool
	{
		if (! empty($input['password'])) {
			$user->password = Hash::make($input['password']);
		}

		if (! $user->save()) {
			return false;
		}

		return true;
	}

	/**
	 * update user information
	 *
	 * @param ReferralUser  $customer
	 * @param array $input
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 */
	public function updateInformation(ReferralUser $customer, array $input): ReferralUser
	{
		$get_customer = Customer::where('user_id', $customer->id)->first();

		if (! $get_customer) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		if (isset($input['notifications']) && count($input['notifications']) > 0) {

			$defaultNotifications = [
				'login'        => 'no',
				'sender_id'    => 'no',
				'keyword'      => 'no',
				'subscription' => 'no',
				'promotion'    => 'no',
				'profile'      => 'no',
			];

			$notifications          = array_merge($defaultNotifications, $input['notifications']);
			$input['notifications'] = json_encode($notifications);
		}

		$data = $get_customer->update($input);

		if (! $data) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		return $customer;
	}


	/**
	 * update permissions
	 *
	 * @param ReferralUser  $customer
	 * @param array $input
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 */
	public function permissions(ReferralUser $customer, array $input): ReferralUser
	{
		$data = array_values($input['permissions']);

		$status = $customer->user->customer()->update([
			'permissions' => json_encode($data),
		]);

		if (! $status) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		return $customer;
	}


	/**
	 * remove permission(s) from user's permissions
	 *
	 * @param ReferralUser  $customer
	 * @param array $permissions
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 */
	public function removePermissions(ReferralUser $customer, array $permissions): ReferralUser
	{
		$permissionsToRemove = array_values($permissions);
		$customerPermissions = json_decode($customer->user->customer->permissions, false);
		$updatedPermissions = array_diff($customerPermissions, $permissionsToRemove);

		$status = $customer->user->customer()->update([
			'permissions' => json_encode($updatedPermissions),
		]);

		if (! $status) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		return $customer;
	}


	/**
	 * add permission(s) from user's permissions
	 *
	 * @param ReferralUser  $customer
	 * @param array $permissions
	 *
	 * @return ReferralUser
	 * @throws GeneralException
	 */
	public function addPermissions(ReferralUser $customer, array $permissions): ReferralUser
	{
		$permissionsToAdd = array_values($permissions);
		$customerPermissions = json_decode($customer->user->customer->permissions, false) ?? [];
		$updatedPermissions = array_unique(array_merge($customerPermissions, $permissionsToAdd));

		$status = $customer->user->customer()->update([
			'permissions' => json_encode($updatedPermissions),
		]);

		if (! $status) {
			throw new GeneralException(__('locale.exceptions.something_went_wrong'));
		}

		return $customer;
	}


	/**
	 * @param ReferralUser $customer
	 *
	 * @return bool
	 * @throws GeneralException
	 */
	public function destroy(ReferralUser $customer): bool
	{
		if (! $customer->can_delete) {
			throw new GeneralException(__('exceptions.backend.users.first_user_cannot_be_destroyed'));
		}

		if (! $customer->delete()) {
			throw new GeneralException(__('exceptions.backend.users.delete'));
		}

		return true;
	}

	/**
	 * @param array $ids
	 *
	 * @return mixed
	 * @throws Exception|Throwable
	 *
	 */
	public function batchEnable(array $ids): bool
	{
		DB::transaction(function () use ($ids) {
			if ($this->query()->whereIn('uid', $ids)
				->update(['status' => true])
			) {
				return true;
			}

			throw new GeneralException(__('exceptions.backend.users.update'));
		});

		return true;
	}

	/**
	 * @param array $ids
	 *
	 * @return mixed
	 * @throws Exception|Throwable
	 *
	 */
	public function batchDisable(array $ids): bool
	{
		DB::transaction(function () use ($ids) {
			if ($this->query()->whereIn('uid', $ids)
				->update(['status' => false])
			) {
				return true;
			}

			throw new GeneralException(__('exceptions.backend.users.update'));
		});

		return true;
	}


	/*
        |--------------------------------------------------------------------------
        | Version 3.3
        |--------------------------------------------------------------------------
        |
        | Logged in as customer
        |
        */


	/**
	 * @throws GeneralException
	 */
	public function impersonate(ReferralUser $customer)
	{
		if ($customer->is_admin) {
			throw new GeneralException(__('locale.customer.admin_cannot_be_impersonated'));
		}

		$authenticatedUser = auth()->user();

		if ($authenticatedUser->id === $customer->id || Session::get('admin_user_id') === $customer->id) {
			return redirect()->route('admin.home');
		}

		if (! Session::get('admin_user_id')) {
			session(['admin_user_id' => $authenticatedUser->id]);
			session(['admin_user_name' => $authenticatedUser->displayName()]);
			session(['temp_user_id' => $customer->id]);

			$permissions = collect(json_decode($customer->user->customer->permissions, true));
			session(['permissions' => $permissions]);
			$customer->update([
				'active_portal' => 'customer',
			]);
		}

		//Login user
		auth()->loginUsingId($customer->id);

		return redirect(Helper::home_route());
	}

	public function getCustomerStats()
	{
		return $this->query()->select(
			DB::raw('COUNT(*) as total_customers'),
			DB::raw('SUM(sms_unit) as total_user_balances'),
			DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_customers'),
			DB::raw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive_customers')
		)
			->first();
	}
}
