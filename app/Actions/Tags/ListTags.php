<?php

declare(strict_types=1);

namespace App\Actions\Tags;

use App\Actions\Traits\AsAction;
use App\Models\Tag as TagModel;
use App\Values\Collections\TagCollection;
use App\Values\Tag;

class ListTags
{
    use AsAction;

    public function handle(array|string $orderBy = ['name']): TagCollection
    {
        $query = TagModel::query();

        if (is_string($orderBy)) {
            $orderBy = [$orderBy];
        }

        foreach ($orderBy as $field) {
            $direction = 'asc';
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            }
            $query->orderBy($field, $direction);
        }

        $tags = $query->get();

        return Tag::collect($tags);
    }
}
