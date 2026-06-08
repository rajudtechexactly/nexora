<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivers the password-reset token. The client submits {email, token,
 * password} back to POST /auth/reset-password.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Deep link the mobile app can intercept; the raw token is also shown
        // so it can be entered manually if the link cannot be opened.
        $deepLink = sprintf(
            '%s/reset-password?token=%s&email=%s',
            rtrim((string) config('app.url'), '/'),
            $this->token,
            urlencode($notifiable->getEmailForPasswordReset()),
        );

        return (new MailMessage)
            ->subject('Reset your Nexora password')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We received a request to reset your password.')
            ->action('Reset Password', $deepLink)
            ->line('Or enter this code in the app: '.$this->token)
            ->line('The code expires in 60 minutes. If you did not request this, you can ignore this email.');
    }
}
