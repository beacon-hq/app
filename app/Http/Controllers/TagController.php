<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tags\CreateTag;
use App\Actions\Tags\FindTag;
use App\Actions\Tags\ListTags;
use App\Actions\Tags\UpdateTag;
use App\Values\Tag;
use Bag\Attributes\WithoutValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Tag::class);

        return Inertia::render('Tags/Index', [
            'tags' => ListTags::run(),
        ]);
    }

    public function store(Tag $tag): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        CreateTag::run($tag);

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
        Tag $tag
    ): Response {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/Edit', [
            'tag' => FindTag::run($tag->id),
        ]);
    }

    public function update(Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag);

        UpdateTag::run($tag);

        return redirect()->route('tags.index')->with(
            'alert',
            [
                'message' => 'Tag updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
