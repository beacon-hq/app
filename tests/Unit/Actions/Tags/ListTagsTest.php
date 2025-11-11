<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Tags\ListTags;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::beginTransaction();

    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create tags with various properties for testing sorting
    $this->tagB = Tag::factory()->for($this->team)->create([
        'name' => 'B Tag',
        'description' => 'Second in alphabetical order by name',
        'color' => '#00ff00',
        'created_at' => now()->subDays(2),
    ]);

    $this->tagA = Tag::factory()->for($this->team)->create([
        'name' => 'A Tag',
        'description' => 'First in alphabetical order by name',
        'color' => '#ff0000',
        'created_at' => now()->subDays(3),
    ]);

    $this->tagC = Tag::factory()->for($this->team)->create([
        'name' => 'C Tag',
        'description' => 'Third in alphabetical order by name',
        'color' => '#0000ff',
        'created_at' => now()->subDays(1),
    ]);

    $this->listTagsAction = ListTags::make();
});

afterEach(function () {
    DB::rollBack();
});

it('returns all tags sorted by name in ascending order by default', function () {
    $result = $this->listTagsAction->handle();

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('A Tag')
        ->and($result[1]->name)->toBe('B Tag')
        ->and($result[2]->name)->toBe('C Tag');
});

it('can sort tags by a specified field in ascending order', function () {
    // Sort by created_at ascending (oldest first)
    $result = $this->listTagsAction->handle('created_at');

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('A Tag') // Oldest
        ->and($result[1]->name)->toBe('B Tag')
        ->and($result[2]->name)->toBe('C Tag'); // Newest
});

it('can sort tags by a specified field in descending order', function () {
    // Sort by name descending
    $result = $this->listTagsAction->handle('-name');

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('C Tag')
        ->and($result[1]->name)->toBe('B Tag')
        ->and($result[2]->name)->toBe('A Tag');

    // Sort by created_at descending (newest first)
    $result = $this->listTagsAction->handle('-created_at');

    expect($result)->toHaveCount(3)
        ->and($result[0]->name)->toBe('C Tag') // Newest
        ->and($result[1]->name)->toBe('B Tag')
        ->and($result[2]->name)->toBe('A Tag'); // Oldest
});

it('can sort tags by multiple fields with different directions', function () {
    // We'll use color field for primary sorting and created_at for secondary sorting
    // Create two tags with the same color but different created_at times
    $tagD1 = Tag::factory()->for($this->team)->create([
        'name' => 'D Tag 1',
        'description' => 'Fourth tag, created second',
        'color' => '#f8dc43', // Same color for both D tags
        'created_at' => now()->subHours(2), // Newer
    ]);

    $tagD2 = Tag::factory()->for($this->team)->create([
        'name' => 'D Tag 2',
        'description' => 'Fifth tag, created first',
        'color' => '#f8dc43', // Same color for both D tags
        'created_at' => now()->subHours(3), // Older
    ]);

    // Sort by color ascending, then by created_at descending (newest first)
    $result = $this->listTagsAction->handle(['color', '-created_at']);

    // Expected order:
    // 1. #0000ff (C Tag)
    // 2. #00ff00 (B Tag)
    // 3-4. #f8dc43 (D Tags - sorted by created_at descending)
    // 5. #ff0000 (A Tag)

    expect($result)->toHaveCount(5)
        // First sorted by color
        ->and($result[0]->name)->toBe('C Tag') // #0000ff
        ->and($result[1]->name)->toBe('B Tag') // #00ff00
        // The two D tags with same color should be sorted by created_at descending
        ->and($result[2]->name)->toBe('D Tag 1') // Newer D tag
        ->and($result[3]->name)->toBe('D Tag 2') // Older D tag
        ->and($result[4]->name)->toBe('A Tag') // #ff0000
        // Verify that the newer D tag comes before the older one
        ->and($result[2]->createdAt->getTimestamp())->toBeGreaterThan($result[3]->createdAt->getTimestamp());
});

it('returns an empty collection when no tags exist', function () {
    // Delete all tags
    Tag::query()->delete();

    $result = $this->listTagsAction->handle();

    expect($result)->toHaveCount(0)
        ->and($result)->toBeEmpty();
});
