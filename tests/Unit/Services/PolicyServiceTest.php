<?php

declare(strict_types=1);

use App\Models\Policy;
use App\Models\Team;
use App\Services\PolicyService;

covers(PolicyService::class);

it('counts policies', function () {
    $team = Team::factory()->create();
    $policy = Policy::factory()->for($team)->create();

    $policyService = resolve(PolicyService::class);
    $found = $policyService->findById($policy->id);
    expect($found->id)->toBe($policy->id);
});
