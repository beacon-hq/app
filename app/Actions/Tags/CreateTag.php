<?php

declare(strict_types=1);

namespace App\Actions\Tags;

use App\Actions\Traits\AsAction;
use App\Models\Tag as TagModel;
use App\Values\Tag;

class CreateTag
{
    use AsAction;

    public function handle(Tag $tag): Tag
    {
        $model = TagModel::create($tag->toArray());

        return Tag::from($model);
    }
}
