<?php

declare(strict_types=1);

namespace App\Services;

use Resend;
use Stringable;

class MailService
{
    public function addContact(Stringable|string $email): void
    {
        $resend = Resend::client(\config('resend.api_key'));
        $resend->contacts->create(
            audienceId: config('resend.audience_id'),
            parameters: [
                'email' => (string) $email,
                'unsubscribed' => false
            ]
        );
    }
}
