<?php

declare(strict_types=1);

namespace App\Actions\Mail;

use App\Actions\Traits\AsAction;
use Resend\Client;
use Stringable;

class AddContact
{
    use AsAction;

    public function __construct(protected Client $client)
    {
    }

    public function handle(Stringable|string $email): void
    {
        $this->client->contacts()->create(
            audienceId: config('resend.audience_id'),
            parameters: [
                'email' => (string) $email,
                'unsubscribed' => false
            ]
        );
    }
}
