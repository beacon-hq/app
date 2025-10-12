<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Actions\AppContext\GetAppContext;
use App\Actions\Traits\AsAction;
use App\Models\AccessToken;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use App\Models\Organization;

class CheckOnboardingStatus
{
    use AsAction;

    public function handle(): bool
    {
        $organization = GetAppContext::organization();
        if ($organization->onboardedAt !== null || Organization::find($organization->id)?->onboarded_at !== null) {
            return false;
        }

        if (Application::count() === 0 || Environment::count() === 0 || FeatureFlag::count() === 0 || FeatureFlagUsage::count() === 0 || AccessToken::count() === 0) {
            return true;
        }

        return false;
    }
}
