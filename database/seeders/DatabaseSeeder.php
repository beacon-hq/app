<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Lottery;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Application::factory(18)->create();

        FeatureType::factory(4)->sequence(
            ['name' => 'Release', 'icon' => 'Rocket', 'color' => 'green', 'description' => 'Manage the rollout of new changes to your application.'],
            ['name' => 'Operational', 'icon' => 'Wrench', 'color' => 'sky', 'description' => 'Gate functionality based on operational concerns.'],
            ['name' => 'Kill Switch', 'icon' => 'Unplug', 'color' => 'red', 'description' => 'Disabling functionality in an emergency.'],
            ['name' => 'Experiment', 'icon' => 'FlaskConical', 'color' => 'indigo', 'description' => 'Test new features with a subset of users.'],
        )->create();

        FeatureFlag::factory(100)
            ->state(new Sequence(
                fn () => ['feature_type_id' => FeatureType::inRandomOrder()->first()->id]
            ))
            ->create();

        Environment::factory(3)->sequence(
            ['name' => 'local'],
            ['name' => 'staging'],
            ['name' => 'production'],
        )->create();

        Tag::factory(15)->create();

        FeatureFlag::all()->each(function (FeatureFlag $flag) {
            Lottery::odds(8, 10)->winner(fn () => $flag->environments()->attach(Environment::inRandomOrder()->first()))->choose();
            Lottery::odds(8, 10)->winner(fn () => $flag->applications()->attach(Application::inRandomOrder()->first()))->choose();
            Lottery::odds(1, 4)->winner(function () use ($flag) {
                try {
                    $flag->tags()->attach(Tag::inRandomOrder()->first());
                } catch (UniqueConstraintViolationException) {
                }
            })->choose(3);
        });
    }
}
