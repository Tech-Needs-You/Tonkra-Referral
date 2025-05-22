<?php

namespace Tonkra\Referral\Repositories\Contracts;

use Tonkra\Referral\Models\ReferralUser;
use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;

/**
 * Interface CustomerRepository.
 */
interface ReferralCustomerRepository extends ReferralBaseRepository
{

    /**
     * @param  array  $input
     * @param  bool  $confirmed
     *
     * @return mixed
     */
    public function store(array $input, bool $confirmed = false);

    /**
     * @param  ReferralUser  $customer
     * @param  array  $input
     *
     * @return mixed
     */
    public function update(ReferralUser $customer, array $input);

    /**
     * @param  ReferralUser  $customer
     * @param  array  $input
     *
     * @return mixed
     */
    public function updateInformation(ReferralUser $customer, array $input);

    /**
     * @param  ReferralUser  $customer
     * @param  array  $input
     *
     * @return mixed
     */
    public function permissions(ReferralUser $customer, array $input);

    /**
     * @param  ReferralUser  $customer
     * @param  array  $permissions
     *
     * @return mixed
     */
    public function addPermissions(ReferralUser $customer, array $permissions);

    /**
     * @param  ReferralUser  $customer
     * @param  array  $permissions
     *
     * @return mixed
     */
    public function removePermissions(ReferralUser $customer, array $permissions);

    /**
     * @param  ReferralUser  $customer
     *
     * @return mixed
     */
    public function destroy(ReferralUser $customer);

    /**
     * @param  array  $ids
     *
     * @return mixed
     */
    public function batchEnable(array $ids);

    /**
     * @param  array  $ids
     *
     * @return mixed
     */
    public function batchDisable(array $ids);

    /**
     * @param  ReferralUser  $customer
     *
     * @return mixed
     */
    public function impersonate(ReferralUser $customer);

    public function getCustomerStats();

}
