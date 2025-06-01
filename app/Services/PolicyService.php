<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PolicyRepository;
use App\Values\Collections\PolicyCollection;
use App\Values\Policy;

class PolicyService
{
    public function __construct(
        protected PolicyRepository $policyRepository
    ) {
    }

    public function all(array|string $orderBy = ['name']): PolicyCollection
    {
        $policies = $this->policyRepository->all($orderBy);

        return Policy::collect($policies);
    }

    public function create(Policy $policy): Policy
    {
        $createdPolicy = $this->policyRepository->create($policy);

        return Policy::from($createdPolicy);
    }

    public function update(Policy $policy): Policy
    {
        $updatedPolicy = $this->policyRepository->update($policy);

        return Policy::from($updatedPolicy);
    }

    public function find(string $id): Policy
    {
        $policy = $this->policyRepository->find($id);

        return Policy::from($policy);
    }
}
