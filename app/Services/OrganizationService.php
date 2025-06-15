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
        $organizationModel = $this->organizationRepository->create($user, $organization);

        return Organization::from(
            id: $organizationModel->id,
            owner: $organizationModel->owner !== null ? User::from($organizationModel->owner) : null,
            name: $organizationModel->name
        );
    }

    public function update(Organization $organization): Organization
    {
        $updatedOrganization = $this->organizationRepository->update($organization);

        return Organization::from(
            id: $updatedOrganization->id,
            owner: $updatedOrganization->owner !== null ? User::from($updatedOrganization->owner) : null,
            name: $updatedOrganization->name
        );
    }

    public function all(User $user): OrganizationCollection
    {
        $organizations = $this->organizationRepository->all($user);

        return Organization::collect($organizations);
    }

    public function findById(?string $id): Organization
    {
        $organization = $this->organizationRepository->findById($id);

        return Organization::from(
            id: $organization->id,
            owner: $organization->owner !== null ? User::from($organization->owner) : null,
            name: $organization->name
        );
    }

    public function delete(Organization $organization): void
    {
        $this->organizationRepository->delete($organization);
    }
}
