<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use App\Modules\Auth\Models\EmailOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivers a one-time verification code by email. Implements ShouldQueue, so
 * Laravel pushes the actual send onto the queue (database connection) and a
 * queue worker processes it out-of-band — registration/reset requests return
 * immediately instead of blocking on SMTP.
 */
class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $purpose,
        private readonly int $expiresInMinutes,
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isReset = $this->purpose === EmailOtp::PURPOSE_PASSWORD_RESET;

        $subject = $isReset
            ? 'Your Nexora password reset code'
            : 'Your Nexora verification code';

        $intro = $isReset
            ? 'Use the code below to reset your Nexora password.'
            : 'Welcome to Nexora! Use the code below to verify your email and finish signing in.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hi '.$notifiable->name.',')
            ->line($intro)
            ->line('Your verification code is:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in '.$this->expiresInMinutes.' minutes.')
            ->line('If you did not request this, you can safely ignore this email.');
    }
}
