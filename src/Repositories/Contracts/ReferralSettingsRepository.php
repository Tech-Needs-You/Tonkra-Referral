<?php

namespace Tonkra\Referral\Repositories\Contracts;

/* *
 * Interface SettingsRepository
 */

interface ReferralSettingsRepository extends ReferralBaseRepository
{

    /**
     *REFERRAL
     *
     * @param  array  $input
     *
     * @return mixed
     */
    public function saveReferralSettings(array $input);
}
