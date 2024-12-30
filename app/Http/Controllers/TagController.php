<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function store(TagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('tags.index')->with(
            'alert',
            [
                'message' => 'Feature type created successfully.',
                'status' => 'success',
            ]
        );
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->all());

        return redirect()->route('tags.index')->with(
            'alert',
            [
                'message' => 'Tag updated successfully.',
                'status' => 'success',
            ]
        );
    }
}
