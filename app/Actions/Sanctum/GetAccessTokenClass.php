<?php

declare(strict_types=1);

namespace App\Actions\Sanctum;

use App\Actions\Traits\AsAction;
use App\Models\AccessToken;

class GetAccessTokenClass
{
    use AsAction;

    public function handle()
    {
        return AccessToken::class;
    }
}
