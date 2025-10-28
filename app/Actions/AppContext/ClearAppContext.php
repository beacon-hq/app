<?php

declare(strict_types=1);

namespace App\Actions\AppContext;

use App\Actions\Traits\AsAction;

class ClearAppContext
{
    use AsAction;

    public function handle(): void
    {
        SetOrganizationContext::run(null);
        SetTeamContext::run(null);
    }
}
