<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TagRepository;
use App\Values\Collections\TagCollection;
use App\Values\Tag;
use Illuminate\Support\Collection;

class TagService
{
    public function __construct(
        protected TagRepository $tagRepository
    ) {
    }

    public function all(array|string $orderBy = ['name']): TagCollection
    {
        $tags = $this->tagRepository->all($orderBy);

        return Tag::collect($tags);
    }

    public function create(Tag $tag): Tag
    {
        $createdTag = $this->tagRepository->create($tag);

        return Tag::from($createdTag);
    }

    public function update(Tag $tag): Tag
    {
        $updatedTag = $this->tagRepository->update($tag);

        return Tag::from($updatedTag);
    }

    public function find(string ...$id): Tag|TagCollection
    {
        $result = $this->tagRepository->find(...$id);

        if ($result instanceof Collection) {
            return Tag::collect($result);
        }

        return Tag::from($result);
    }
}
