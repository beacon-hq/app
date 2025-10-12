<?php

declare(strict_types=1);

use App\Actions\Applications\FindApplication;
use App\Models\Application as ApplicationModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Unit\Actions\AppContext\SetAppContext;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    $this->application = ApplicationModel::factory()->for($this->team)->create([
        'name' => 'test-app',
        'display_name' => 'Test App',
        'description' => 'Test application',
        'color' => '#ff0000',
    ]);

    $this->findApplicationAction = FindApplication::make();
});

it('can find an application by id', function () {
    $result = $this->findApplicationAction->handle($this->application->id);

    expect($result->id)->toBe($this->application->id)
        ->and($result->name)->toBe('test-app')
        ->and($result->displayName)->toBe('Test App')
        ->and($result->description)->toBe('Test application')
        ->and($result->color)->toBe('#ff0000');
});

it('throws an exception when application does not exist', function () {
    $this->findApplicationAction->handle('non-existent-id');
})->throws(ModelNotFoundException::class);
