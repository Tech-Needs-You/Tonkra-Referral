<?php

namespace Tonkra\Referral\Repositories\Eloquent;

use App\Models\AppConfig;
use App\Models\Customer;
use Tonkra\Referral\Facades\ReferralSettings;
use Tonkra\Referral\Helpers\Helper;
use Tonkra\Referral\Models\Referral;
use Tonkra\Referral\Repositories\Contracts\ReferralAnnouncementsRepository;
use Tonkra\Referral\Repositories\Contracts\ReferralSettingsRepository;

class EloquentReferralSettingsRepository extends EloquentReferralBaseRepository implements ReferralSettingsRepository
{
    protected ReferralAnnouncementsRepository $announcements;
    /**
     * EloquentSettingsRepository constructor.
     */
    public function __construct(AppConfig $app_config, ReferralAnnouncementsRepository $announcements)
    {
        parent::__construct($app_config);
        $this->announcements = $announcements;
    }

    // Version 3.10.0

    /**
     * REFERRAL
     *
     *
     * @return bool
     */
    public function saveReferralSettings(array $input)
    {
        $old_status = ReferralSettings::status();

        Referral::setEnv(Referral::REFERRAL_STATUS, $input['status']);
        Referral::setEnv(Referral::REFERRAL_BONUS, $input['bonus']);
        Referral::setEnv(Referral::REFERRAL_EMAIL_NOTIFICATION, $input['email_notification']);
        Referral::setEnv(Referral::REFERRAL_SMS_NOTIFICATION, $input['sms_notification']);

        $new_status = (bool)$input['status'];
        $permissions_to_add = [Referral::PERMISSION_VIEW_REFERRAL];

        if (!$new_status) {
            Helper::removePermissions($permissions_to_add);
        } else {
            Helper::addPermissions($permissions_to_add);
        }

        if ($old_status !== $new_status) {
            // Create announcement (notification) when status changes
            $announcement_data = [
                "customer" => "0",
                "title" => strtoupper(__('referral::locale.referrals.announcement_title')),
                "description" => __('referral::locale.referrals.announcement_description', ['status' => $new_status? strtoupper(__('referral::locale.referrals.activated')) : strtoupper(__('referral::locale.referrals.deactivated'))]),
                "send_email" => "yes",
                "send_by" => "send_by_email",
            ];

            $this->announcements->store($announcement_data);
        }
    }
}
