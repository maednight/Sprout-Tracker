<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends the password reset email notification.
 */
class SproutResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * @var string Password reset token included in the email.
     */
    private readonly string $token;

    /**
     * @param string $token Password reset token included in the email.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password_reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expirationMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Your Sprout Password')
            ->view('emails.password-reset', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'expirationMinutes' => $expirationMinutes,
                'logoUrl' => asset('projectassets/images/logo/sprout-logo.svg'),
            ]);
    }
}
