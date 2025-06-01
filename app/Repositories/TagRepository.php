<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Tag;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class TagRepository
{
    public function all(array|string $orderBy = ['name']): Collection
    {
        $query = Tag::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return $query->get();
    }

    public function create(TagValue $tag): Tag
    {
        return Tag::create($tag->toArray());
    }

    public function update(TagValue $tag): Tag
    {
        $tagModel = Tag::findOrFail($tag->id);

        $tagModel->update(
            $tag
                ->toCollection()
                ->except('id')
                ->toArray()
        );

        return $tagModel->fresh();
    }

    public function find(string ...$id): Collection|Tag
    {
        $tags = Tag::whereIn('id', Arr::wrap($id))->get();

        if ($tags->isEmpty()) {
            throw (new ModelNotFoundException())->setModel(Tag::class);
        }

        if (count($id) === 1) {
            return $tags->first();
        }

        return $tags;
    }
}
