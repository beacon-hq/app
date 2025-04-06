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

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $verificationUrl = $this->verificationUrl();

        return $this->buildMailMessage($verificationUrl);
    }

    protected function buildMailMessage(string $url): MailMessage
    {
        return (new MailMessage())
            ->subject(sprintf('You\'ve been invited to join the "%s" team on Beacon!', $this->invite->team->name))
            ->line(sprintf('You have been invited to join the team "%s" on Beacon by %s!', $this->invite->team->name, $this->invite->user->firstName))
            ->line('Please click the button below to complete your registration.')
            ->action('Register Now', $url)
            ->line(sprintf('The invitation expires in %s.', CarbonInterval::instance(config('beacon.teams.invitation_expiration'))));
    }

    protected function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'teams.accept-invite',
            $this->invite->expires_at,
            [
                'id' => $this->invite->id,
                'team' => $this->invite->team->id,
            ]
        );
    }
}
