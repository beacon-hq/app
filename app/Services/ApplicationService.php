<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ApplicationRepository;
use App\Values\Application;
use App\Values\Collections\ApplicationCollection;

class ApplicationService
{
    public function __construct(protected ApplicationRepository $applicationRepository)
    {
    }

    public function all(): ApplicationCollection
    {
        return $this->applicationRepository->all();
    }

    public function create(Application $application): Application
    {
        return $this->applicationRepository->create($application);
    }

    public function update(Application $application): Application
    {
        return $this->applicationRepository->update($application);
    }

    public function findBySlug(?string $slug): Application
    {
        return $this->applicationRepository->findBySlug($slug);
    }
}
