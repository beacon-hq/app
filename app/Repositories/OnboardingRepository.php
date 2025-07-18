<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AccessToken;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use App\Models\Organization;
use App\Services\AccessTokenService;
use App\Values\AccessToken as AccessTokenValue;
use Illuminate\Support\Facades\App;

class OnboardingRepository
{
    public function __construct(protected AccessTokenService $accessTokenService)
    {
    }

    public function getOnboardingStatus(): bool
    {
        if (App::context()->organization->onboardedAt !== null || Organization::find(App::context()->organization->id)?->onboarded_at !== null) {
            return false;
        }

        if (Application::count() === 0 || Environment::count() === 0 || FeatureFlag::count() === 0 || FeatureFlagUsage::count() === 0 || AccessToken::count() === 0) {
            return true;
        }

        return false;
    }

    public function getAccessToken(): string
    {
        if ($accessToken = AccessToken::first()) {
            return $accessToken->token_value;
        }

        return $this->accessTokenService->create(AccessTokenValue::from(name: 'Beacon Onboarding'))->token;
    }

    public function completeOnboarding(): void
    {
        Organization::find(App::context()->organization->id)->update(['onboarded_at' => now()]);
    }
}
