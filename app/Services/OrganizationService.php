<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrganizationRepository;
use App\Values\Collections\OrganizationCollection;
use App\Values\Organization;
use App\Values\User;

class OrganizationService
{
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
    }

    public function create(User $user, Organization $organization): Organization
    {
        return $this->organizationRepository->create($user, $organization);
    }

    public function update(Organization $organization): Organization
    {
        return $this->organizationRepository->update($organization);
    }

    public function all(User $user): OrganizationCollection
    {
        return $this->organizationRepository->all($user);
    }

    public function findById(?string $id): Organization
    {
        return $this->organizationRepository->findById($id);
    }

    public function delete(Organization $organization)
    {
        $this->organizationRepository->delete($organization);
    }
}
