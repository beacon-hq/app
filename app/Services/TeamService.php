<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Values\AppContext;
use App\Values\Team;

class TeamService
{
    public function __construct(protected TeamRepository $teamRepository, protected AppContext $appContext)
    {
    }

    public function findById(string $id): Team
    {
        return $this->teamRepository->findById($id);
    }

    public function create(Team $from): Team
    {
        return $this->teamRepository->create($from);
    }

    public function findBySlug(string $slug)
    {
        return $this->teamRepository->findBySlug($slug);
    }
}
