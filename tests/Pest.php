<?php

declare(strict_types=1);

use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use function Pest\Laravel\freezeTime;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        freezeTime();
        Sleep::fake();
        Sleep::whenFakingSleep(function (CarbonInterval $duration) {
            // Progress time when faking sleep...
            $this->travel($duration->totalMilliseconds)->milliseconds();
        });
        Http::preventStrayRequests();
    })
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/
expect()->extend('toBeAnonymousClass', function () {
    if (! is_object($this->value)) {
        return expect(false)->toBeTrue();
    }

    return expect((new \ReflectionClass($this->value))->isAnonymous())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function property(object $object, string $property)
{
    $closure = function () use ($property) {
        return $this->{$property};
    };

    return $closure->bindTo($object, $object)();
}
