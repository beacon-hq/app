<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\Applications\FindApplicationByName;
use App\Models\Application as ApplicationModel;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->application = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'test-app',
        'display_name' => 'Test App',
        'description' => 'Test application',
        'color' => '#ff0000',
    ]);

    $this->findApplicationByNameAction = FindApplicationByName::make();
});

it('can find an application by name', function () {
    $result = $this->findApplicationByNameAction->handle('test-app');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($this->application->id)
        ->and($result->name)->toBe('test-app')
        ->and($result->displayName)->toBe('Test App')
        ->and($result->description)->toBe('Test application')
        ->and($result->color)->toBe('#ff0000');
});

it('returns null when application does not exist', function () {
    $result = $this->findApplicationByNameAction->handle('non-existent-app');

    expect($result)->toBeNull();
});
