<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\AppContext;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
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
