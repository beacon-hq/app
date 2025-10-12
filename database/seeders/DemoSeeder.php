<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\AppContext\SetOrganizationContext;
use App\Actions\AppContext\SetTeamContext;
use App\Models\Activity;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureFlagUsage;
use App\Models\FeatureType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Lottery;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'me@daveyshafik.com')->first();
        $team = $user->teams()->first();
        $organization = $team->organization;

        SetOrganizationContext::run($organization);
        SetTeamContext::run($team);

        Auth::login($user);

        Application::factory()->count(\random_int(5, 15))
            ->for($team)
            ->create();

        Environment::factory()->count(\random_int(3, 7))
            ->for($team)
            ->create();

        FeatureFlag::factory()
            ->count(\random_int(150, 225))
            ->state([
                'team_id' => $team->id,
                'status' => Lottery::odds(8, 10)->choose(),
            ])
            ->state(new Sequence(
                function (Sequence $sequence) {
                    $types = collect([
                        'Release' => random_int(70, 90),
                        'Experiment' => random_int(20, 30),
                        'Operational' => random_int(1, 10),
                        'Kill Switch' => random_int(1, 5),
                    ]);

                    $rand = \random_int(1, $types->sum());
                    $accum = 0;
                    $idx = -1;
                    $types->each(function ($d, $k) use (&$accum, &$idx, $rand) {
                        $idx = $k;
                        $accum += $d;
                        if ($accum >= $rand) {
                            return false;
                        }

                        return true;
                    });

                    return ['feature_type_id' => FeatureType::where('name', $idx)->first()->id];
                }
            ))
            ->create();

        for ($i = 0; $i < \random_int(300, 500); $i++) {
            while (true) {
                $featureFlag = FeatureFlag::inRandomOrder()->first()->id;
                $application = Application::inRandomOrder()->first()->id;
                $environment = Environment::inRandomOrder()->first()->id;
                if (!FeatureFlagStatus::where('feature_flag_id', $featureFlag)
                    ->where('application_id', $application)
                    ->where('environment_id', $environment)
                    ->exists()) {
                    break;
                }
            }

            FeatureFlagStatus::factory()
                ->state([
                    'status' => Lottery::odds(3, 10)->choose(),
                ])
                ->state(
                    new Sequence(function () use ($featureFlag, $environment, $application) {
                        return [
                            'feature_flag_id' => $featureFlag,
                            'application_id' => $application,
                            'environment_id' => $environment,
                        ];
                    })
                )
                ->create();
        }

        for ($i = 0; $i < 7; $i++) {
            FeatureFlagStatus::all()->each->update(['status' => false]);
            FeatureFlagStatus::all()->each->update(['status' => true]);
        }

        Activity::all()->each(fn ($row) => $row->update([
            'created_at' => now()->subDays(\random_int(0, 35))
                ->subHours(\random_int(0, 23))
                ->subMinutes(\random_int(0, 59)),
        ]));

        DB::select(
            <<<EOS
            INSERT INTO feature_flag_feature_flag_status (
                id, feature_flag_id, feature_flag_status_id, created_at, updated_at
            )
            SELECT
               generate_ulid(),
               feature_flag_id,
               id,
               NOW(),
               NOW()
           FROM feature_flag_statuses
           EOS
        );

        FeatureFlagUsage::factory()
            ->count(\random_int(10000, 30000))
            ->for($team)
            ->state(new Sequence(
                function () {
                    $featureFlag = FeatureFlag::inRandomOrder()->first();
                    $application = Application::inRandomOrder()->first();
                    $environment = Environment::inRandomOrder()->first();

                    return [
                        'feature_flag_id' => $featureFlag->id,
                        'application_id' => $application->id,
                        'environment_id' => $environment->id,
                        'active' => Lottery::odds(7, 10)->choose(),
                        'evaluated_at' => Lottery::odds(7, 10)
                            ->winner(fn () => now()->subDays(\random_int(0, 30))->subHours(\random_int(0, 23))->subMinutes(\random_int(0, 59)))
                            ->loser(fn () => now()->subDays(\random_int(0, 180))->subHours(\random_int(0, 23))->subMinutes(\random_int(0, 59)))
                            ->choose(),
                        'feature_flag_name' => $featureFlag->name,
                        'application_name' => $application->name,
                        'environment_name' => $environment->name,
                    ];
                }
            ))
            ->create();

        FeatureFlagStatus::query()
            ->whereIn(
                'feature_flag_id',
                FeatureFlag::query()
                    ->inRandomOrder()
                    ->limit(\random_int(30, 75))
                    ->pluck('id')
                    ->toArray()
            )
            ->delete();

        DB::delete('DELETE FROM feature_flag_usages WHERE feature_flag_id IN (\'' . FeatureFlag::query()
            ->inRandomOrder()
            ->limit(\random_int(30, 75))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->pluck('id')
            ->implode('\',\'') . '\')');

        $featureFlag = FeatureFlag::query()->orderBy('name')->first();
        $application = Application::query()->orderBy('name')->first();
        $environment = Environment::query()->orderBy('name')->first();

        FeatureFlagUsage::factory()
            ->state([
                'team_id' => $team->id,
                'feature_flag_id' => $featureFlag->id,
                'application_id' => $application->id,
                'environment_id' => $environment->id,
                'feature_flag_name' => $featureFlag->name,
                'application_name' => $application->name,
                'environment_name' => $environment->name,
            ])
            ->state(new Sequence(function () {
                return [
                    'active' => Lottery::odds(7, 10)->choose(),
                    'evaluated_at' => Lottery::odds(7, 10)
                        ->winner(fn () => now()->subDays(\random_int(0, 10))->subHours(\random_int(0, 23))->subMinutes(\random_int(0, 59)))
                        ->loser(fn () => now()->subDays(\random_int(0, 35))->subHours(\random_int(0, 23))->subMinutes(\random_int(0, 59)))
                        ->choose(),
                    'value' => Arr::random(['red', 'green', 'blue']),
                ];
            }))
            ->count(\random_int(5000, 10000))
            ->create();

        FeatureFlag::query()
            ->join('feature_types', 'feature_types.id', '=', 'feature_flags.feature_type_id')
            ->where('temporary', true)
            ->where(function (Builder $query) {
                $query->where('status', true)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('feature_flag_statuses')
                            ->whereColumn('feature_flag_statuses.feature_flag_id', 'feature_flags.id')
                            ->where('feature_flag_statuses.status', true);
                    });
            })
            ->limit(random_int(3, 7))
            ->update(['last_seen_at' => now()->subDays(35)]);

        FeatureFlag::query()
            ->whereFeatureType(FeatureType::where('name', 'Experiment')->first()->id)
            ->inRandomOrder()
            ->limit(random_int(3, 7))
            ->update(['completed_at' => now()]);

        FeatureFlag::query()
            ->whereFeatureType(FeatureType::where('name', 'Experiment')->first()->id)
            ->inRandomOrder()
            ->limit(random_int(3, 7))
            ->update(['completed_at' => now()->subMonth()]);

    }
}
