<?php

namespace Tonkra\Referral\Notifications;

use App\Library\Tool;
use App\Models\EmailTemplates;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReferralNotification extends Notification
{
	use Queueable;

	protected User $downliner;
	protected string $url;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct($downliner, $url)
	{
		$this->downliner   = $downliner;
		$this->url         = $url;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return array
	 */
	public function via($notifiable): array
	{
		return ['mail'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 *
	 * @return MailMessage
	 */
	public function toMail($notifiable): MailMessage
	{

		$template = EmailTemplates::where('slug', 'new_downline_notification')->first();

		$subject = Tool::renderTemplate($template->subject, [
			'app_name' => config('app.name'),
		]);

		$content = Tool::renderTemplate($template->content, [
			'upliner_name'     => $notifiable->displayName(),
			'downliner_name'   => $this->downliner->displayName(),
			'registered_at'    => $this->downliner->created_at,
			'url' => "<a href='$this->url' target='_blank'>View</a>",
		]);

		return (new MailMessage)
			->from(config('mail.from.address'), config('mail.from.name'))
			->subject($subject)
			->markdown('referral::emails.new_downline', ['content' => $content, 'url' => $this->url]);
	}
}
