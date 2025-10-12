<?php

declare(strict_types=1);

use App\Actions\Mail\AddContact;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Resend\Client;
use Resend\Contracts\Client as ClientContract;
use Resend\Service\Contact;

it('adds a new contact', function () {
    app()->singleton(ClientContract::class, fn () => mock(Client::class, function (MockInterface $mock) {
        $mock->shouldReceive('contacts')->andReturn(mock(Contact::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->withArgs(function (string $audienceId, array $parameters) {
                return $audienceId === 'testing' && $parameters === [
                    'email' => 'user@example.org',
                    'unsubscribed' => false
                ];
            })->once();
        }));
    }));

    Config::set('resend.audience_id', 'testing');
    resolve(AddContact::class)->handle('user@example.org');
});
