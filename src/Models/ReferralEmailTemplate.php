<?php

namespace Tonkra\Referral\Models;

use App\Library\Traits\HasUid;
use App\Models\EmailTemplates;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $uid)
 * @method static truncate()
 * @method static create(array $tp)
 * @property mixed name
 */
class ReferralEmailTemplate extends EmailTemplates
{

    public function template_tags($template): array
    {
        $tags = [];
        switch ($template) {

            case 'customer_registration':
                $tags['app_name']      = 'required';
                $tags['first_name']    = 'optional';
                $tags['last_name']     = 'optional';
                $tags['login_url']     = 'required';
                $tags['email_address'] = 'required';
                $tags['password']      = 'optional';
                break;

            case 'registration_verification':
                $tags['app_name']         = 'required';
                $tags['verification_url'] = 'required';
                break;

            case 'password_reset':
                $tags['app_name']      = 'optional';
                $tags['first_name']    = 'optional';
                $tags['last_name']     = 'optional';
                $tags['login_url']     = 'required';
                $tags['email_address'] = 'required';
                $tags['password']      = 'required';
                break;


            case 'forgot_password':
                $tags['app_name']             = 'optional';
                $tags['forgot_password_link'] = 'required';
                break;

            case 'login_notification':
                $tags['app_name']   = 'required';
                $tags['time']       = 'required';
                $tags['ip_address'] = 'required';
                break;

            case 'registration_notification':
                $tags['app_name']             = 'optional';
                $tags['first_name']           = 'optional';
                $tags['last_name']            = 'optional';
                $tags['customer_profile_url'] = 'required';
                break;

            case 'sender_id_notification':
                $tags['app_name']      = 'optional';
                $tags['sender_id']     = 'required';
                $tags['customer_name'] 		= 'optional';
                $tags['customer_phone'] 		= 'optional';
                $tags['customer_email'] 		= 'optional';
                $tags['description']    = 'optional';
                $tags['status']   		= 'optional';
                $tags['date']    		= 'optional';
                $tags['sender_id_url'] = 'required';
                break;

            case 'subscription_notification':
                $tags['app_name']    		= 'optional';
                $tags['admin_name']    		= 'optional';
                $tags['customer_name'] 		= 'optional';
                $tags['amount']   			= 'optional';
                $tags['type']    			= 'optional';
                $tags['plan_name']    		= 'optional';
                $tags['payment_method']     = 'optional';
                $tags['payment_type']    	= 'optional';
                $tags['transaction_id']     = 'optional';
                $tags['status']   				= 'optional';
                $tags['date']    					= 'optional';
                $tags['invoice_url'] 			= 'required';
                break;

            case 'keyword_purchase_notification':
                $tags['app_name']    = 'optional';
                $tags['keyword_url'] = 'required';
                break;

            case 'number_purchase_notification':
                $tags['app_name']   = 'optional';
                $tags['number_url'] = 'required';
                break;

            case 'sender_id_confirmation':
                $tags['app_name']      = 'optional';
                $tags['user_name']     = 'optional';
                $tags['sender_id']     = 'optional';
                $tags['status']        = 'required';
                $tags['sender_id_url'] = 'required';
                break;

                //            case 'ticket_customer':
                //                $tags['app_name']       = 'optional';
                //                $tags['first_name']     = 'optional';
                //                $tags['last_name']      = 'optional';
                //                $tags['ticket_url']     = 'required';
                //                $tags['ticket_id']      = 'optional';
                //                $tags['ticket_subject'] = 'optional';
                //                $tags['message']        = 'optional';
                //                $tags['create_by']      = 'optional';
                //                break;
                //
                //            case 'reply_ticket':
                //                $tags['app_name']       = 'optional';
                //                $tags['first_name']     = 'optional';
                //                $tags['last_name']      = 'optional';
                //                $tags['ticket_url']     = 'required';
                //                $tags['ticket_id']      = 'required';
                //                $tags['ticket_subject'] = 'optional';
                //                $tags['message']        = 'optional';
                //                $tags['reply_by']       = 'optional';
                //                break;
                //
                //            case 'ticket_admin':
                //                $tags['app_name']        = 'optional';
                //                $tags['department_name'] = 'optional';
                //                $tags['ticket_url']      = 'required';
                //                $tags['ticket_id']       = 'required';
                //                $tags['ticket_subject']  = 'optional';
                //                $tags['message']         = 'optional';
                //                $tags['create_by']       = 'optional';
                //                break;

            case 'admin_campaign_notice':
               $tags['app_name']        	= 'optional';
               $tags['customer_name'] 		= 'optional';
               $tags['admin_name']      	= 'optional';
               $tags['campaign_title']  	= 'optional';
               $tags['total_recipients']  = 'optional';
               $tags['start_date']        = 'optional';
               $tags['campaign_id']       = 'optional';
               $tags['contact_groups']    = 'optional';
               $tags['campaign_url']      = 'required';
               break;

           case 'admin_topup_notice':
               $tags['app_name']        	= 'optional';
               $tags['customer_name'] 		= 'optional';
               $tags['admin_name']      	= 'optional';
               $tags['amount']     				= 'optional';
               $tags['sms_unit']  				= 'optional';
               $tags['date']        			= 'optional';
               $tags['type']       				= 'optional';
               $tags['payment_method']    = 'optional';
               $tags['transaction_id']    = 'optional';
               $tags['url']      					= 'required';
               break;

            // Custom Version 3.10.0

            case 'new_downline_notification':
                $tags['app_name']               = 'optional';
                $tags['upliner_name']           = 'optional';
                $tags['downliner_name']         = 'optional';
                $tags['registered_at']          = 'optional';
                $tags['url']                    = 'required';
                break;

            case 'referral_bonus_notification':
                $tags['app_name']               = 'optional';
                $tags['upliner_name']           = 'optional';
                $tags['downliner_name']         = 'optional';
                $tags['transaction_type']       = 'optional';
                $tags['bonus']                  = 'optional';
                $tags['available_bonus']        = 'optional';
                $tags['date']                   = 'optional';
                $tags['url']                    = 'required';
                break;

            case 'new_user_registration':
                $tags['app_name']               = 'optional';
                $tags['new_user_email']         = 'required';
                $tags['admin_name']             = 'optional';
                $tags['new_user_name']          = 'optional';
                $tags['referrer_name']          = 'optional';
                $tags['referrer_email']         = 'optional';
                $tags['date']                   = 'optional';
                $tags['login_url']              = 'required';
                break;
        }

        return $tags;
    }

}
