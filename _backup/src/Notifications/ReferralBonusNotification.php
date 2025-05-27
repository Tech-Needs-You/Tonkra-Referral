<?php

namespace Tonkra\Referral\Notifications;

use App\Library\Tool;
use App\Models\EmailTemplates;
use App\Models\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tonkra\Referral\Models\ReferralBonus;
use Tonkra\Referral\Models\ReferralUser;

class ReferralBonusNotification extends Notification
{
	use Queueable;

	protected ReferralBonus $bonus;
	protected Invoices $invoice;
	protected string $url;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct($bonus, $invoice, $url)
	{
		$this->bonus   = $bonus;
		$this->invoice   = $invoice;
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

		$template = EmailTemplates::where('slug', 'referral_bonus_notification')->first();

		$subject = Tool::renderTemplate($template->subject, [
			'bonus' => $this->bonus->bonus,
			'app_name' => config('app.name'),
		]);

		$content = Tool::renderTemplate($template->content, [
			'upliner_name'     => $notifiable->displayName(),
			'downliner_name'   => ReferralUser::find($this->bonus->from)->displayName(),
			'transaction_type'    => $this->invoice->type,
			'bonus'    => $this->bonus->bonus,
			'available_bonus'    => $notifiable->paidReferralBonuses()->sum('bonus'),
			'url' => "<a href='$this->url' target='_blank'>View</a>",
		]);

		return (new MailMessage)
			->from(config('mail.from.address'), config('mail.from.name'))
			->subject($subject)
			->markdown('referral::emails.new_downline', ['content' => $content, 'url' => $this->url]);
	}
}
