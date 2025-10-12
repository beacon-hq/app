<?php

declare(strict_types=1);

use App\Actions\Traits\AsAction;

arch()
    ->expect('App\Actions\*')
    ->classes()
    ->toUseTrait(AsAction::class)
    ->ignoring([
        'App\Actions\Fortify',
    ]);
