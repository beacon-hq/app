<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Tags\CreateTag;
use App\Models\Tag;
use App\Values\Tag as TagValue;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->createTagAction = CreateTag::make();
});

afterEach(function () {
    DB::rollBack();
});

it('can create a tag', function () {
    $tag = TagValue::from(
        id: '',
        name: 'Test Tag',
        description: 'Tag for testing',
        color: '#ff0000'
    );

    $result = $this->createTagAction->handle($tag);

    // Verify the tag was created in the database
    $model = Tag::where('name', 'Test Tag')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('Test Tag')
        ->and($model->description)->toBe('Tag for testing')
        ->and($model->color)->toBe('#ff0000')
        ->and($model->team_id)->toBe($this->team->id)
        ->and($result->name)->toBe('Test Tag')
        ->and($result->description)->toBe('Tag for testing')
        ->and($result->color)->toBe('#ff0000')
        ->and($result->id)->toBe($model->id);
});

it('can create a tag with minimal properties', function () {
    $tag = TagValue::from(
        id: '',
        name: 'Minimal Tag',
        color: '#00ff00'
    );

    $result = $this->createTagAction->handle($tag);

    // Verify the tag was created in the database
    $model = Tag::where('name', 'Minimal Tag')->first();

    expect($model)->not->toBeNull()
        ->and($model->name)->toBe('Minimal Tag')
        ->and($model->description)->toBeNull()
        ->and($model->color)->toBe('#00ff00')
        ->and($result->name)->toBe('Minimal Tag')
        ->and($result->has('description'))->toBeFalse() // Value object should have optional description
        ->and($result->color)->toBe('#00ff00');
});
