<?php

namespace Tonkra\Referral\Notifications;

use App\Library\Tool;
use App\Models\EmailTemplates;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
{
    use Queueable;

    protected User $new_user;
    protected ?User $referrer;
    protected string $login_url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $new_user, ?User $referrer = null, $login_url)
    {
        $this->new_user     = $new_user;
        $this->referrer     = $referrer;
        $this->login_url    = $login_url;
    }

    /**
     * Get the notification's delivery channels.
     *
     *
     * @return array
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     *
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {

        $template = EmailTemplates::where('slug', 'new_user_registration')->first();

        $subject = Tool::renderTemplate($template->subject, [
                'app_name' => config('app.name'),
        ]);

        $content = Tool::renderTemplate($template->content, [
                'app_name'          => config('app.name'),
                'admin_name'        => $notifiable->displayName(),
                'new_user_email'    => $this->new_user->email,
                'new_user_name'     => $this->new_user->displayName(),
                'referrer_name'     => $this->referrer?->displayName(),
                'referrer_email'    => $this->referrer?->email,
                'date'              => $this->new_user->created_at,
                'login_url'         => "<a href='$this->login_url' target='_blank'>".__('locale.auth.login')."</a>",
        ]);

        return (new MailMessage)
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->subject($subject)
                ->markdown('emails.customer.welcome', ['content' => $content, 'url' => $this->login_url]);
    }
}
