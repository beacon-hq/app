<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\Organization;

class CompleteOnboarding
{
    use AsAction;

    public function handle(): void
    {
        Organization::find(GetAppContext::organization()->id)->update(['onboarded_at' => now()]);
    }
}
