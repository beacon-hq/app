<?php

declare(strict_types=1);

arch('it does not call dd()')->expect('dd')->not->toBeUsed();
arch('it does not call ddd()')->expect('ddd')->not->toBeUsed();
arch('it does not call dump()')->expect('dump')->not->toBeUsed();
arch('it does not call debug()')->expect('debug')->not->toBeUsed();
arch('it does not call xdebug_break()')->expect('xdebug_break')->not->toBeUsed();

arch()->preset()->php();

arch()->preset()->laravel()->ignoring(['App\Enums\Traits']);

arch()->expect('App')->toUseStrictTypes()->toUseStrictEquality();
