<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->laravel()->ignoring(['App\Enums\Traits']);

arch()->expect('App')->toUseStrictTypes()->toUseStrictEquality();
