<?php

declare(strict_types=1);

use App\Enums\Traits\AsValue;

arch()->expect('App\\Enums')->enums()->toUseTrait(AsValue::class);
arch()->expect('App\Enums')->toBeEnums()->ignoring('App\Enums\Traits');
arch()->expect('App\Enums\Traits')->toBeTraits();
