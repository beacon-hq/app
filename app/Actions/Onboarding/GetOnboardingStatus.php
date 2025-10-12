<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Actions\Traits\AsAction;

class GetOnboardingStatus
{
    use AsAction;

    /**
     * @return array{onboarding: bool, accessToken: ?string}
     */
    public function handle(): array
    {
        $onboardingStatus = CheckOnboardingStatus::run();

        return [
            'onboarding' => $onboardingStatus,
            'accessToken' => ($onboardingStatus) ? GetAccessToken::run() : null,
        ];
    }
}
