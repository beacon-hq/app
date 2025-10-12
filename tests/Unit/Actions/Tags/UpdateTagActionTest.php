<?php

declare(strict_types=1);

use App\Actions\Tags\UpdateTag;
use App\Models\Tag;
use App\Values\Tag as TagValue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create a tag to update
    $this->tag = Tag::factory()->for($this->team)->create([
        'name' => 'Original Tag',
        'description' => 'Original description',
        'color' => '#ff0000',
    ]);

    $this->updateTagAction = UpdateTag::make();
});

afterEach(function () {
    DB::rollBack();
});

it('can update a tag', function () {
    $tag = TagValue::from(
        id: $this->tag->id,
        name: 'Updated Tag',
        description: 'Updated description',
        color: '#00ff00'
    );

    $result = $this->updateTagAction->handle($tag);

    // Refresh the model from the database
    $this->tag->refresh();

    // Verify the tag was updated in the database
    expect($this->tag->name)->toBe('Updated Tag')
        ->and($this->tag->description)->toBe('Updated description')
        ->and($this->tag->color)->toBe('#00ff00')
        ->and($result->name)->toBe('Updated Tag')
        ->and($result->description)->toBe('Updated description')
        ->and($result->color)->toBe('#00ff00')
        ->and($result->id)->toBe($this->tag->id);
});

it('can update a tag with optional description', function () {
    $tag = TagValue::from(
        id: $this->tag->id,
        name: 'Updated Tag',
        description: '',
        color: '#0000ff'
    );

    $result = $this->updateTagAction->handle($tag);

    // Refresh the model from the database
    $this->tag->refresh();

    // Verify the tag was updated in the database
    expect($this->tag->name)->toBe('Updated Tag')
        ->and($this->tag->description)->toBe('') // Model should have empty string description
        ->and($this->tag->color)->toBe('#0000ff')
        ->and($result->name)->toBe('Updated Tag')
        ->and($result->description)->toBe('') // Value object should have empty string description
        ->and($result->color)->toBe('#0000ff')
        ->and($result->id)->toBe($this->tag->id);
});

it('throws an exception when tag does not exist', function () {
    $tag = TagValue::from(
        id: 'non-existent-id',
        name: 'Non-existent Tag',
        description: 'This tag does not exist',
        color: '#ff00ff'
    );

    $this->updateTagAction->handle($tag);
})->throws(ModelNotFoundException::class);
