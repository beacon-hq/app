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
        $applications = $this->applicationRepository->all();

        return Application::collect($applications);
    }

    public function create(Application $application): Application
    {
        $createdApplication = $this->applicationRepository->create($application);

        return Application::from($createdApplication);
    }

    public function update(Application $application): Application
    {
        $updatedApplication = $this->applicationRepository->update($application);

        return Application::from($updatedApplication);
    }

    public function find(string $id): Application
    {
        $application = $this->applicationRepository->find($id);

        return Application::from($application);
    }
}
