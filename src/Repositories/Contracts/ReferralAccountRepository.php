<?php

namespace Tonkra\Referral\Repositories\Contracts;

use Tonkra\Referral\Repositories\Contracts\ReferralBaseRepository;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Interface AccountRepository.
 */
interface ReferralAccountRepository extends ReferralBaseRepository
{
    /**
     * @param  array  $input
     *
     * @return mixed
     */
    public function register(array $input);


    /**
     * @param $provider
     * @param $data
     *
     * @return mixed
     */
    public function findOrCreateSocial($provider, $data);

    /**
     * @param  Authenticatable  $user
     * @param                                            $name
     *
     * @return bool
     */
    public function hasPermission(Authenticatable $user, $name): bool;

    /**
     * @param  array  $input
     *
     * @return mixed
     */
    public function update(array $input);

    /**
     * @return mixed
     */
    public function delete();


    /**
     * @param  Authenticatable  $user
     *
     * @return mixed
     */
    public function redirectAfterLogin(Authenticatable $user);

    public function payPayment(array $input);
}
