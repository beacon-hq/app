<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TagService;
use App\Values\Tag;
use Bag\Attributes\WithoutValidation;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(TagService $tagService)
    {
        Gate::authorize('viewAny', Tag::class);

        return Inertia::render('Tags/Index', [
            'tags' => $tagService->all(),
        ]);
    }

    public function store(Tag $tag, TagService $tagService)
    {
        Gate::authorize('create', Tag::class);

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
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/Edit', [
            'tag' => $tagService->findBySlug($tag->slug),
        ]);
    }

    public function update(Tag $tag, TagService $tagService)
    {
        Gate::authorize('update', $tag);

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
