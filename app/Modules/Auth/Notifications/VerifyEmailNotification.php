<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Sends a signed, expiring email-verification link. The mobile app can open
 * the link directly (it hits the API verify endpoint) or embed it in a webview.
 */
class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your Nexora email address')
            ->greeting('Welcome to Nexora, '.$notifiable->name.'!')
            ->line('Please confirm your email address to activate your account.')
            ->action('Verify Email', $url)
            ->line('This link expires in 60 minutes. If you did not create an account, no action is required.');
    }

    private function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'api.auth.email.verify',
            Carbon::now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }
}
