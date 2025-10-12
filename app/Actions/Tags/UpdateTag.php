<?php

declare(strict_types=1);

namespace App\Actions\Tags;

use App\Actions\Traits\AsAction;
use App\Models\Tag as TagModel;
use App\Values\Tag;

class UpdateTag
{
    use AsAction;

    public function handle(Tag $tag): Tag
    {
        $model = TagModel::findOrFail($tag->id);
        $model->name = $tag->name;
        $model->description = $tag->description;
        $model->color = $tag->color;
        $model->save();

        return Tag::from($model);
    }
}
