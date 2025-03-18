<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Values\Invite;
use Carbon\CarbonInterval;
use function config;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class RegistrationInvite extends Notification
{
    public function __construct(protected Invite $invite)
    {
    }

    /**
     * Get the notification's channels.
     *
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(sprintf('You\'ve been invited to join the "%s" team on Beacon!', $this->invite->team->name))
            ->line(sprintf('You have been invited to join the team "%s" on Beacon by %s!', $this->invite->team->name, $this->invite->user->firstName))
            ->line('Please click the button below to complete your registration.')
            ->action('Register Now', $url)
            ->line(sprintf('The invitation expires in %s.', CarbonInterval::instance(config('beacon.teams.invitation_expiration'))));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'teams.accept-invite',
            $this->invite->expires_at,
            [
                'id' => $this->invite->id,
                'slug' => $this->invite->team->slug,
            ]
        );
    }
}
