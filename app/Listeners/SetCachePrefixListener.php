<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TeamChangedEvent;
use Illuminate\Support\Facades\Config;

class SetCachePrefixListener
{
    public function handle(TeamChangedEvent $event): void
    {
        Config::set('cache.prefix', 'app_' . $event->team->id);
    }
}
