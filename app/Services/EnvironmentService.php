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
        return $this->environmentRepository->all();
    }

    public function create(Environment $environment): Environment
    {
        return $this->environmentRepository->create($environment);
    }

    public function update(Environment $environment): Environment
    {
        return $this->environmentRepository->update($environment);
    }

    public function find(string $id)
    {
        return $this->environmentRepository->find($id);
    }
}
