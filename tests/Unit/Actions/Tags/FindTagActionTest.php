<?php

declare(strict_types=1);

use App\Actions\Tags\FindTag;
use App\Models\Tag;
use App\Values\Collections\TagCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create tags for testing
    $this->tagA = Tag::factory()->for($this->team)->create([
        'name' => 'Tag A',
        'description' => 'Description for Tag A',
        'color' => '#ff0000',
    ]);

    $this->tagB = Tag::factory()->for($this->team)->create([
        'name' => 'Tag B',
        'description' => 'Description for Tag B',
        'color' => '#00ff00',
    ]);

    $this->tagC = Tag::factory()->for($this->team)->create([
        'name' => 'Tag C',
        'description' => 'Description for Tag C',
        'color' => '#0000ff',
    ]);

    $this->findTagAction = FindTag::make();
});

afterEach(function () {
    DB::rollBack();
});

it('can find a single tag by id', function () {
    $result = $this->findTagAction->handle($this->tagA->id);

    expect($result->id)->toBe($this->tagA->id)
        ->and($result->name)->toBe('Tag A')
        ->and($result->description)->toBe('Description for Tag A')
        ->and($result->color)->toBe('#ff0000');
});

it('can find multiple tags by their ids', function () {
    $result = $this->findTagAction->handle($this->tagA->id, $this->tagB->id, $this->tagC->id);

    expect($result)->toBeInstanceOf(TagCollection::class)
        ->and($result)->toHaveCount(3)
        ->and($result->pluck('id'))->toContain($this->tagA->id)
        ->and($result->pluck('id'))->toContain($this->tagB->id)
        ->and($result->pluck('id'))->toContain($this->tagC->id);

    // Find Tag B in the result and verify its properties
    $tagB = $result->firstWhere('id', $this->tagB->id);

    expect($tagB->name)->toBe('Tag B')
        ->and($tagB->description)->toBe('Description for Tag B')
        ->and($tagB->color)->toBe('#00ff00');
});

it('throws an exception when a single tag does not exist', function () {
    $this->findTagAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);

it('throws an exception when one of multiple tags does not exist', function () {
    $this->findTagAction->handle($this->tagA->id, 'non-existent-id', $this->tagC->id);
})->throws(ModelNotFoundException::class, 'Tag(s) not found: non-existent-id');
