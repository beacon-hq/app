<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Lottery;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $team = Team::factory(['name' => 'Davey\'s Team'])->create();
        App::context(team: $team);

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

        User::factory()
            ->hasAttached($team)
            ->create([
                'first_name' => 'Davey',
                'last_name' => 'Shafik',
                'email' => 'davey@php.net',
                'password' => 'qtf7vnd!ejn5TEN*dbh',
            ]);
    }
}
