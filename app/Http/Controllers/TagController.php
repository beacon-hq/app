<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TagService;
use App\Values\Tag;
use Bag\Attributes\WithoutValidation;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(TagService $tagService)
    {
        return Inertia::render('Tags/Index', [
            'tags' => $tagService->all(),
        ]);
    }

    public function store(Tag $tag, TagService $tagService)
    {
        $tagService->create($tag);

        return redirect()->route('tags.index')->with(
            'alert',
            [
                'message' => 'Tag created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(
        #[WithoutValidation]
        Tag $tag,
        TagService $tagService
    ) {
        return Inertia::render('Tags/Edit', [
            'tag' => $tagService->findBySlug($tag->slug),
        ]);
    }

    public function update(Tag $tag, TagService $tagService)
    {
        $tagService->update($tag);

        return redirect()->route('tags.index')->with(
            'alert',
            [
                'message' => 'Tag updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
