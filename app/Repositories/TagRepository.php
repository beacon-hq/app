<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Tag;
use App\Values\Collections\TagCollection;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class TagRepository
{
    public function all(array|string $orderBy = ['name']): TagCollection
    {
        $query = Tag::query();

        foreach (Arr::wrap($orderBy) as $column) {
            $query = $query->orderBy($column);
        }

        return TagValue::collect($query->get());
    }

    public function create(TagValue $tag): TagValue
    {
        return TagValue::from(Tag::create($tag->toArray()));
    }

    public function update(TagValue $tag): TagValue
    {
        Tag::where('slug', $tag->slug)->firstOrFail()->update(
            $tag
                ->toCollection()
                ->except('slug')
                ->toArray()
        );

        return $tag;
    }

    public function findBySlug(string ...$slug): TagValue|TagCollection
    {
        $tags = Tag::whereIn('slug', Arr::wrap($slug))->get();

        if ($tags->isEmpty()) {
            throw (new ModelNotFoundException())->setModel(Tag::class);
        }

        if (count($slug) === 1) {
            return TagValue::from($tags->first());
        }

        return TagValue::collect($tags);
    }
}
