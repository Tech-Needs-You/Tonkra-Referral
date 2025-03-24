<?php

namespace Tonkra\Referral\Repositories\Contracts;

use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;
use Tonkra\Referral\Models\ReferralUser;

/**
 * Interface UserRepository.
 */
interface ReferralUserRepository extends ReferralBaseRepository
{
    /**
     * @param array $input
     * @param  bool  $confirmed
     *
     * @return mixed
     */
    public function store(array $input, bool $confirmed = false);

    /**
     * @param ReferralUser  $user
     * @param array $input
     *
     * @return mixed
     */
    public function update(ReferralUser $user, array $input);

    /**
     * @param ReferralUser $user
     *
     * @return mixed
     */
    public function destroy(ReferralUser $user);

    /**
     * @param ReferralUser $user
     *
     * @return mixed
     */
    public function impersonate(ReferralUser $user);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchDestroy(array $ids);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchEnable(array $ids);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function batchDisable(array $ids);
}
