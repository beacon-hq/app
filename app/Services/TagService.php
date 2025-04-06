<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TagRepository;
use App\Values\Collections\TagCollection;
use App\Values\Tag;

class TagService
{
    public function __construct(
        protected TagRepository $tagRepository
    ) {
    }

    public function all(array|string $orderBy = ['name']): TagCollection
    {
        return $this->tagRepository->all($orderBy);
    }

    public function create(Tag $policy): Tag
    {
        return $this->tagRepository->create($policy);
    }

    public function update(Tag $policy): Tag
    {
        return $this->tagRepository->update($policy);
    }

    public function find(string ...$id): Tag|TagCollection
    {
        return $this->tagRepository->find(...$id);
    }
}
