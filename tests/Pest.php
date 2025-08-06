<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use App\Services\UserService;
use Bag\Internal\Cache;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Laravel\Dusk\Browser;
use Pest\Arch\Support\Composer;
use function Pest\Laravel\freezeTime;
use Pest\TestSuite;
use Tests\DuskTestCase;
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
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function () {
        freezeTime();

        Sleep::fake();
        Sleep::whenFakingSleep(function (CarbonInterval $duration) {
            // Progress time when faking sleep...
            $this->travel($duration->totalMilliseconds)->milliseconds();
        });

        Http::preventStrayRequests();

        Cache::reset();
    })
    ->in('Unit', 'Feature');

pest()->extend(DuskTestCase::class)
    ->in('Browser')
    ->beforeAll(function () {
        Artisan::call('migrate:fresh');
    })
    ->afterEach(fn () => $this->browse(function (Browser $browser) {
        $browser->logout();
    }));
;

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

function vendorPackages(): array
{
    $namespaces = [];
    $rootPath = TestSuite::getInstance()->rootPath.DIRECTORY_SEPARATOR;

    foreach (Composer::loader()->getPrefixesPsr4() as $namespace => $directories) {
        foreach ($directories as $directory) {
            $directory = realpath($directory);

            if ($directory === false) {
                continue;
            }

            if (substr_count($namespace, '\\') > 1) {
                continue;
            }

            if (str_starts_with($directory, $rootPath.'vendor')) {
                $namespaces[] = rtrim($namespace, '\\');
            }
        }
    }

    return $namespaces;
}

function createBrowserUser(): User
{
    $user = app(UserService::class)->create(
        \App\Values\User::from(...User::factory(['first_name' => 'Beacon Demo'])->make()->toArray(), password: 'Password123!'),
    )->id;

    $userModel = User::find($user);
    $userModel->forceFill([
        'email_verified_at' => now(),
        'two_factor_confirmed_at' => now(),
        'two_factor_secret' => 'testing',
        'two_factor_recovery_codes' => 'testing',
    ])->save();

    DB::commit();

    return $userModel;
}

function createOwner(): array
{
    $team = Team::factory()->create();
    $user = User::factory()->hasAttached($team)->create();
    $user->assignRole(Role::OWNER);

    return [$team, $user];
}
