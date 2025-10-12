<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Onboarding\CheckOnboardingStatus;
use App\Actions\Onboarding\CompleteOnboarding;
use Illuminate\Http\JsonResponse;

class OnboardingController
{
    public function status(): JsonResponse
    {
        return response()->json(['onboarding' => CheckOnboardingStatus::run()]);
    }

    public function complete()
    {
        CompleteOnboarding::run();

        return response()->noContent();
    }
}
