<?php

declare(strict_types=1);

namespace App\Actions\Tags;

use App\Actions\Traits\AsAction;
use App\Models\Tag as TagModel;
use App\Values\Collections\TagCollection;
use App\Values\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindTag
{
    use AsAction;

    public function handle(string ...$ids): Tag|TagCollection
    {
        if (count($ids) === 1) {
            $tag = TagModel::findOrFail($ids[0]);

            return Tag::from($tag);
        }

        $tags = TagModel::whereIn('id', $ids)->get();

        if ($tags->count() !== count($ids)) {
            $missingIds = array_diff($ids, $tags->pluck('id')->toArray());

            throw new ModelNotFoundException(
                'Tag(s) not found: ' . implode(', ', $missingIds)
            );
        }

        return Tag::collect($tags);
    }
}
