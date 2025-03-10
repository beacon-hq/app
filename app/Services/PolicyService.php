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
        return $this->policyRepository->all($orderBy);
    }

    public function create(Policy $policy): Policy
    {
        return $this->policyRepository->create($policy);
    }

    public function update(Policy $policy): Policy
    {
        return $this->policyRepository->update($policy);
    }

    public function findBySlug(string $slug): Policy
    {
        return $this->policyRepository->findBySlug($slug);
    }

    public function findById(string $id): Policy
    {
        return $this->policyRepository->findById($id);
    }
}
