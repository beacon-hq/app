<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EnvironmentRepository;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Environment;

class EnvironmentService
{
    public function __construct(protected EnvironmentRepository $environmentRepository)
    {
    }

    public function all(): EnvironmentCollection
    {
        $environments = $this->environmentRepository->all();

        return Environment::collect($environments);
    }

    public function create(Environment $environment): Environment
    {
        $createdEnvironment = $this->environmentRepository->create($environment);

        return Environment::from($createdEnvironment);
    }

    public function update(Environment $environment): Environment
    {
        $updatedEnvironment = $this->environmentRepository->update($environment);

        return Environment::from($updatedEnvironment);
    }

    public function find(string $id): Environment
    {
        $environment = $this->environmentRepository->find($id);

        return Environment::from($environment);
    }
}
