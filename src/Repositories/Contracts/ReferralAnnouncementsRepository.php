<?php

namespace Tonkra\Referral\Repositories\Contracts;

/* *
 * Interface AnnouncementsRepository
 */

use App\Models\Announcements;

interface ReferralAnnouncementsRepository extends ReferralBaseRepository
{
    /**
     * @return mixed
     */
    public function store(array $input);

    /**
     * @return mixed
     */
    public function update(Announcements $announcements, array $input);

    /**
     * @return mixed
     */
    public function destroy(Announcements $announcements);

    /**
     * @return mixed
     */
    public function batchDestroy(array $ids);
}
