<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OnboardingRepository;

class OnboardingService
{
    public function __construct(protected OnboardingRepository $onboardingRepository)
    {
    }

    /**
     * @return array{onboarding: bool, accessToken: ?string}
     */
    public function onboardingStatus(): array
    {
        $onboardingStatus = $this->onboardingRepository->getOnboardingStatus();

        return [
            'onboarding' => $onboardingStatus,
            'accessToken' => ($onboardingStatus) ? $this->onboardingRepository->getAccessToken() : null,
        ];
    }

    public function completeOnboarding()
    {
        $this->onboardingRepository->completeOnboarding();
    }
}
