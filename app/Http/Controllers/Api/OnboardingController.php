<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;

class OnboardingController
{
    public function __construct(protected OnboardingService $onboardingService)
    {
    }

    public function status(): JsonResponse
    {
        return response()->json(['onboarding' => $this->onboardingService->onboardingStatus()['onboarding']]);
    }

    public function complete()
    {
        $this->onboardingService->completeOnboarding();

        return response()->noContent();
    }
}
