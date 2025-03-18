<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionType;
use App\Enums\Role as RoleType;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Lottery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $subPermissions = collect(['create', 'update', 'delete', 'view']);

        collect(PermissionType::values())->each(function ($permission) use ($subPermissions) {
            return $subPermissions->each(fn ($perm) => Permission::create(['name' => $permission . '.' . $perm]));
        });

        Role::create(['name' => RoleType::OWNER()])->givePermissionTo(Permission::all());

        Role::create(['name' => RoleType::ADMIN()])->givePermissionTo(Permission::all());

        Role::create(['name' => RoleType::DEVELOPER()])->givePermissionTo([
            ... $subPermissions->map(fn ($sub) => PermissionType::FEATURE_FLAGS() . '.' . $sub)->toArray(),
            ... $subPermissions->map(fn ($sub) => PermissionType::FEATURE_TYPES() . '.' . $sub)->toArray(),
            ... $subPermissions->map(fn ($sub) => PermissionType::FEATURE_FLAG_STATUS() . '.' . $sub)->toArray(),
            ... $subPermissions->map(fn ($sub) => PermissionType::APPLICATIONS() . '.' . $sub)->toArray(),
            ... $subPermissions->map(fn ($sub) => PermissionType::ENVIRONMENTS() . '.' . $sub)->toArray(),
            ... $subPermissions->map(fn ($sub) => PermissionType::TAGS() . '.' . $sub)->toArray(),
        ]);

        Role::create(['name' => RoleType::BILLER()])->givePermissionTo([
            ... $subPermissions->map(fn ($sub) => PermissionType::BILLING() . '.' . $sub)->toArray(),
        ]);

        $user = User::factory()
            ->create([
                'first_name' => 'Davey',
                'last_name' => 'Shafik',
                'email' => 'davey@php.net',
                'password' => 'qtf7vnd!ejn5TEN*dbh',
            ]);

        $user->assignRole('owner');

        $team = User::first()->teams()->first();

        Application::factory(18)
            ->for($team)
            ->create();

        FeatureType::factory(4)
            ->for($team)
            ->sequence(
                ['name' => 'Release', 'icon' => 'Rocket', 'color' => 'green', 'description' => 'Manage the rollout of new changes to your application.'],
                ['name' => 'Operational', 'icon' => 'Wrench', 'color' => 'sky', 'description' => 'Gate functionality based on operational concerns.'],
                ['name' => 'Kill Switch', 'icon' => 'Unplug', 'color' => 'red', 'description' => 'Disabling functionality in an emergency.'],
                ['name' => 'Experiment', 'icon' => 'FlaskConical', 'color' => 'indigo', 'description' => 'Test new features with a subset of users.'],
            )
            ->create();

        FeatureFlag::factory(100)
            ->for($team)
            ->state(new Sequence(
                fn () => ['feature_type_id' => FeatureType::inRandomOrder()->first()->id]
            ))
            ->create();

        Environment::factory(3)
            ->for($team)
            ->sequence(
                ['name' => 'local'],
                ['name' => 'staging'],
                ['name' => 'production'],
            )
            ->create();

        Tag::factory(15)
            ->for($team)
            ->create();

        FeatureFlag::all()->each(function (FeatureFlag $flag) {
            Lottery::odds(8, 10)->winner(function () use ($flag) {
                try {
                    $flag->statuses()->attach(FeatureFlagStatus::create([
                        'application_id' => Application::inRandomOrder()->first()->id,
                        'environment_id' => Environment::inRandomOrder()->first()->id,
                        'feature_flag_id' => $flag->id,
                        'status' => Lottery::odds(1, 2)->choose(),
                    ]));
                } catch (UniqueConstraintViolationException) {
                }
            })->choose(3);

            Lottery::odds(1, 4)->winner(function () use ($flag) {
                try {
                    $flag->tags()->attach(Tag::inRandomOrder()->first());
                } catch (UniqueConstraintViolationException) {
                }
            })->choose(3);
        });
    }
}
