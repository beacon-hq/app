<?php

declare(strict_types=1);

use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Models\Policy;
use App\Models\Tag;
use App\Models\Team;
use App\Repositories\FeatureFlagStatusRepository;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Collections\PolicyValueCollection;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\PolicyDefinition;
use App\Values\PolicyValue;
use Bag\Collection;
use Illuminate\Support\Facades\App;

covers(FeatureFlagStatusRepository::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
    $featureType = FeatureType::factory()->for($this->team)->create();
    $tags = Tag::factory(3)->for($this->team)->create();
    $this->application = Application::factory()->for($this->team)->create();
    $this->environment = Environment::factory()->for($this->team)->create();
    $this->featureFlag = FeatureFlag::factory()->for($this->team)->for($featureType)->hasAttached($tags)->create();

    $this->featureFlagStatus = FeatureFlagStatus::factory()->for($this->featureFlag)->for($this->application)->for($this->environment)->create([
        'status' => true,
    ]);

    $this->featureFlag->statuses()->sync([$this->featureFlagStatus]);

    App::context($this->team);
});

it('evaluates scalar value policies', function (array $policyDefinition, array $values, array $context, bool $expected) {
    $policy = Policy::factory()->for($this->team)->create([
        'definition' => PolicyDefinitionCollection::make($policyDefinition),
    ]);

    $this->featureFlagStatus->policies()->attach($policy, [
        'order' => 1,
        'values' => PolicyValueCollection::make($values),
    ]);

    $context = FeatureFlagContext::from($context);

    $featureFlagStatusRepository = new FeatureFlagStatusRepository();
    $result = $featureFlagStatusRepository->first(FeatureFlagValue::from($this->featureFlag), $context);

    expect($result)
        ->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($result->featureFlag)
        ->toBe($this->featureFlag->slug)
        ->and($result->value)
        ->toBeNull()
        ->and($result->active)
        ->toBe($expected);
})->with('generatedScalar');

it('evaluates array value policies', function (array $policyDefinition, array $values, array $context, bool $expected) {
    $policy = Policy::factory()->for($this->team)->create([
        'definition' => PolicyDefinitionCollection::make($policyDefinition),
    ]);

    $this->featureFlagStatus->policies()->attach($policy, [
        'order' => 1,
        'values' => PolicyValueCollection::make($values),
    ]);

    $context = FeatureFlagContext::from($context);

    $featureFlagStatusRepository = new FeatureFlagStatusRepository();
    $result = $featureFlagStatusRepository->first(FeatureFlagValue::from($this->featureFlag), $context);

    expect($result)
        ->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($result->featureFlag)
        ->toBe($this->featureFlag->slug)
        ->and($result->value)
        ->toBeNull()
        ->and($result->active)
        ->toBe($expected);
})->with('generatedArray');

it('does not evaluate invalid policies', function () {
    $policyDefinition = [PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL)];
    $values = [PolicyValue::from($policyDefinition[0], Collection::make(['test']))];
    $context = [
        'scopeType' => 'testing',
        'scope' => ['testing' => fn () => 'test'],
        'appName' => $this->application->name,
        'environment' => $this->environment->name,
    ];

    $policy = Policy::factory()->for($this->team)->create([
        'definition' => PolicyDefinitionCollection::make($policyDefinition),
    ]);

    $this->featureFlagStatus->policies()->attach($policy, [
        'order' => 1,
        'values' => PolicyValueCollection::make($values),
    ]);

    $context = FeatureFlagContext::from($context);

    $featureFlagStatusRepository = new FeatureFlagStatusRepository();
    $result = $featureFlagStatusRepository->first(FeatureFlagValue::from($this->featureFlag), $context);

    expect($result)
        ->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($result->featureFlag)
        ->toBe($this->featureFlag->slug)
        ->and($result->value)
        ->toBeNull()
        ->and($result->active)
        ->toBe(false);
});

dataset('generatedScalar', generateScalarValueTestCases());

dataset('generatedArray', generateArrayValueTestCases());

function generateScalarValueTestCases()
{
    $equalityOperators = [
        PolicyDefinitionMatchOperator::EQUAL,
        PolicyDefinitionMatchOperator::NOT_EQUAL,
        PolicyDefinitionMatchOperator::GREATER_THAN,
        PolicyDefinitionMatchOperator::LESS_THAN,
        PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL,
        PolicyDefinitionMatchOperator::LESS_THAN_EQUAL,
    ];

    $matchOperators = [
        PolicyDefinitionMatchOperator::MATCHES_ALL,
        PolicyDefinitionMatchOperator::NOT_MATCHES_ALL,
    ];

    $containsOperators = [
        PolicyDefinitionMatchOperator::CONTAINS_ALL,
        PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL,
    ];

    $oneOfOperators = [
        PolicyDefinitionMatchOperator::ONE_OF,
        PolicyDefinitionMatchOperator::NOT_ONE_OF,
    ];

    $logicalOperators = [
        Boolean::AND,
        Boolean::OR,
        Boolean::XOR,
        Boolean::NOT,
    ];

    $testCases = [];

    $testCases['single expression when context missing'] = function () {
        $policyDefinition = [PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL)];
        $values = [PolicyValue::from($policyDefinition[0], Collection::make(['invalid']))];
        $context = [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test'],
            'appName' => $this->application->name,
            'environment' => $this->environment->name,
        ];
        $expected = false;

        return [$policyDefinition, $values, $context, $expected];
    };
    $testCases['multiple expression when context missing'] = function () {
        $policyDefinition = [
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', PolicyDefinitionMatchOperator::EQUAL),
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing2', PolicyDefinitionMatchOperator::EQUAL),
        ];

        $values = [
            PolicyValue::from($policyDefinition[0], Collection::make(['not-test'])),
            PolicyValue::from($policyDefinition[1], Collection::make(['not-test2'])),
        ];
        $context = [
            'scopeType' => 'testing',
            'scope' => ['testing' => 'test', 'testing2' => 'test2'],
            'appName' => $this->application->name,
            'environment' => $this->environment->name,
        ];
        $expected = false;

        return [$policyDefinition, $values, $context, $expected];
    };

    foreach ($equalityOperators as $equalityOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator);
        $testCases["single expression match operator {$equalityOperator->name} equals"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([0]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL) || $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) || $equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([1]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) || $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN) || $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} greater than"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([0]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 1],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) || $equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN) || $equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} less than"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([1]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) || $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN) || $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL),
        ];

        // Multiple expressions without logical operator
        $policyDefinition = [
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator),
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing2', $equalityOperator),
        ];

        $testCases["multiple expressions match operator {$equalityOperator->name} without operator both true"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([0])),
                PolicyValue::from($policyDefinition[1], Collection::make([1])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0, 'testing2' => 1],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->isNot(PolicyDefinitionMatchOperator::NOT_EQUAL) && $equalityOperator->isNot(PolicyDefinitionMatchOperator::GREATER_THAN) && $equalityOperator->isNot(PolicyDefinitionMatchOperator::LESS_THAN),
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator both false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([1])),
                PolicyValue::from($policyDefinition[1], Collection::make([2])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0, 'testing2' => 1],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL))
                || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN))
                || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL)),
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator left false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([1])),
                PolicyValue::from($policyDefinition[1], Collection::make([1])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 0, 'testing2' => 1],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL),
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator right false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([1])),
                PolicyValue::from($policyDefinition[1], Collection::make([1])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 1, 'testing2' => 0],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL),
        ];

        foreach ($logicalOperators as $logicalOperator) {
            /** @var PolicyDefinitionMatchOperator $equalityOperator */
            /** @var Boolean $logicalOperator */

            // Multiple expressions with logical operator
            $policyDefinition = [
                PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator),
                PolicyDefinition::from(PolicyDefinitionType::OPERATOR, $logicalOperator->value),
                PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing2', $equalityOperator),
            ];

            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator both true"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([0])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 0, 'testing2' => 1],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($logicalOperator === Boolean::OR || $logicalOperator === Boolean::AND) && $equalityOperator->isNot(PolicyDefinitionMatchOperator::NOT_EQUAL) && $equalityOperator->isNot(PolicyDefinitionMatchOperator::GREATER_THAN) && $equalityOperator->isNot(PolicyDefinitionMatchOperator::LESS_THAN),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator both false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([1])),
                    PolicyValue::from($policyDefinition[2], Collection::make([2])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 0, 'testing2' => 1],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT])),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator left false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([1])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 0, 'testing2' => 1],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::AND]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL) && $logicalOperator->In([Boolean::XOR, Boolean::OR]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN) && $logicalOperator->notIn([Boolean::AND]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN) && $logicalOperator->notIn([Boolean::AND, Boolean::XOR, Boolean::OR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL) && $logicalOperator->notIn([Boolean::AND, Boolean::NOT])),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator right false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([1])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 1, 'testing2' => 0],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::NOT, Boolean::AND]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL) && $logicalOperator->In([Boolean::XOR, Boolean::OR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN) && $logicalOperator->notIn([Boolean::AND, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::LESS_THAN_EQUAL) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN) && $logicalOperator->notIn([Boolean::AND, Boolean::XOR, Boolean::OR, Boolean::NOT]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::GREATER_THAN_EQUAL) && $logicalOperator->notIn([Boolean::AND])),
            ];
        }
    }

    foreach ($matchOperators as $matchOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $matchOperator);
        $testCases["single expression match operator {$matchOperator->name} equals"] = function () use ($policyDefinition, $matchOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test.*$/']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 'testing'],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::MATCHES_ALL),
            ];
        };
        $testCases["single expression match multiple operator {$matchOperator->name} equals"] = function () use ($policyDefinition, $matchOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test.*$/', '/^testing$/']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 'testing'],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::MATCHES_ALL),
            ];
        };

        $testCases["single expression match operator {$matchOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test$/']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 'testing'],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::NOT_MATCHES_ALL),
        ];

        $testCases["single expression match multiple operator {$matchOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test$/', '/^testing2$/']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 'testing'],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::NOT_MATCHES_ALL),
        ];

        $testCases["single expression match multiple operator {$matchOperator->name} some equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test$/', '/^testing$/']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 'testing'],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => false,
        ];
    }

    foreach ($containsOperators as $containsOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $containsOperator);
        $testCases["single expression match operator {$containsOperator->name} true"] = function () use ($policyDefinition, $containsOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['test']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 'testing'],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::CONTAINS_ALL),
            ];
        };
        $testCases["single expression match operator {$containsOperator->name} false"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['invalid']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 'testing'],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL),
        ];
    }

    foreach ($oneOfOperators as $oneOfOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $oneOfOperator);
        $testCases["single expression match operator {$oneOfOperator->name} true"] = function () use ($policyDefinition, $oneOfOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['test']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => 'test'],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => false,
            ];
        };
        $testCases["single expression match operator {$oneOfOperator->name} false"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['test']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => 'invalid'],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => false,
        ];
    }

    return $testCases;
}

function generateArrayValueTestCases()
{
    $equalityOperators = [
        PolicyDefinitionMatchOperator::EQUAL,
        PolicyDefinitionMatchOperator::NOT_EQUAL,
    ];

    $matchOperators = [
        PolicyDefinitionMatchOperator::MATCHES_ALL,
        PolicyDefinitionMatchOperator::NOT_MATCHES_ALL,
    ];

    $containsOperators = [
        PolicyDefinitionMatchOperator::CONTAINS_ALL,
        PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL,
    ];

    $oneOfOperators = [
        PolicyDefinitionMatchOperator::ONE_OF,
        PolicyDefinitionMatchOperator::NOT_ONE_OF,
    ];

    $logicalOperators = [
        Boolean::AND,
        Boolean::OR,
        Boolean::XOR,
        Boolean::NOT,
    ];

    $testCases = [];

    $testCases['single expression when context missing'] = function () {
        $policyDefinition = [PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'invalid2', PolicyDefinitionMatchOperator::EQUAL)];
        $values = [PolicyValue::from($policyDefinition[0], Collection::make(['test']))];
        $context = [
            'scopeType' => 'testing',
            'scope' => ['testing' => ['test']],
            'appName' => $this->application->name,
            'environment' => $this->environment->name,
        ];
        $expected = false;

        return [$policyDefinition, $values, $context, $expected];
    };
    $testCases['multiple expression when context missing'] = function () {
        $policyDefinition = [
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'invalid', PolicyDefinitionMatchOperator::EQUAL),
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'invalid2', PolicyDefinitionMatchOperator::EQUAL),
        ];

        $values = [
            PolicyValue::from($policyDefinition[0], Collection::make(['test'])),
            PolicyValue::from($policyDefinition[1], Collection::make(['test'])),
        ];
        $context = [
            'scopeType' => 'testing',
            'scope' => ['testing' => ['test', 'test2'], 'testing2' => ['test3', 'test4']],
            'appName' => $this->application->name,
            'environment' => $this->environment->name,
        ];
        $expected = false;

        return [$policyDefinition, $values, $context, $expected];
    };

    foreach ($equalityOperators as $equalityOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator);
        $testCases["single expression match operator {$equalityOperator->name} equals"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([0, 1, 2]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [0, 1, 2]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([0, 1, 2]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [0, 1]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} greater than"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([0, 1, 2]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [1, 2, 3]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL),
        ];
        $testCases["single expression match operator {$equalityOperator->name} less than"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make([1, 2, 3]))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [0, 1, 2]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL),
        ];

        // Multiple expressions without logical operator
        $policyDefinition = [
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator),
            PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing2', $equalityOperator),
        ];

        $testCases["multiple expressions match operator {$equalityOperator->name} without operator both true"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                PolicyValue::from($policyDefinition[1], Collection::make([1, 2, 3])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [0, 1, 2], 'testing2' => [1, 2, 3]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL),
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator both false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                PolicyValue::from($policyDefinition[1], Collection::make([1, 2, 3])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [1, 2, 3], 'testing2' => [0, 1, 2]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL),
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator left false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                PolicyValue::from($policyDefinition[1], Collection::make([1, 2, 3])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [1, 2, 3], 'testing2' => [1, 2, 3]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => false,
        ];
        $testCases["multiple expressions match operator {$equalityOperator->name} without operator right false"] = fn () => [
            'policyDefinition' => $policyDefinition,
            'values' => [
                PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                PolicyValue::from($policyDefinition[1], Collection::make([1, 2, 3])),
            ],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => [0, 1, 2], 'testing2' => [0, 1, 2]],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => false,
        ];

        foreach ($logicalOperators as $logicalOperator) {
            /** @var PolicyDefinitionMatchOperator $equalityOperator */
            /** @var Boolean $logicalOperator */

            // Multiple expressions with logical operator
            $policyDefinition = [
                PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $equalityOperator),
                PolicyDefinition::from(PolicyDefinitionType::OPERATOR, $logicalOperator->value),
                PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing2', $equalityOperator),
            ];

            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator both true"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1, 2, 3])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => [0, 1, 2], 'testing2' => [1, 2, 3]],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($logicalOperator === Boolean::OR || $logicalOperator === Boolean::AND) && !$equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator both false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1, 2, 3])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => [1, 2, 3], 'testing2' => [0, 1, 2]],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::XOR, Boolean::NOT])),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator left false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1, 2, 3])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => [1, 2, 3], 'testing2' => [1, 2, 3]],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::AND]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL) && $logicalOperator->In([Boolean::XOR, Boolean::OR])),
            ];
            $testCases["multiple expressions match operator {$equalityOperator->name} with {$logicalOperator->value} operator right false"] = fn () => [
                'policyDefinition' => $policyDefinition,
                'values' => [
                    PolicyValue::from($policyDefinition[0], Collection::make([0, 1, 2])),
                    PolicyValue::from($policyDefinition[2], Collection::make([1, 2, 3])),
                ],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => [0, 1, 2], 'testing2' => [0, 1, 2]],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => ($equalityOperator->is(PolicyDefinitionMatchOperator::NOT_EQUAL) && $logicalOperator->notIn([Boolean::NOT, Boolean::AND]))
                    || ($equalityOperator->is(PolicyDefinitionMatchOperator::EQUAL) && $logicalOperator->In([Boolean::XOR, Boolean::OR, Boolean::NOT])),
            ];
        }
    }

    foreach ($matchOperators as $matchOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $matchOperator);
        $testCases["single expression single match operator {$matchOperator->name} equals"] = function () use ($policyDefinition, $matchOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test.*$/']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => ['testing', 'test2']],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::MATCHES_ALL),
            ];
        };

        $testCases["single expression multiple match operator {$matchOperator->name} equals"] = function () use ($policyDefinition, $matchOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test.*$/', '/^test(2|ing)$/']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => ['testing', 'test2']],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::MATCHES_ALL),
            ];
        };

        $testCases["single expression single match operator {$matchOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^tes$/']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => ['testing', 'test']],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::NOT_MATCHES_ALL),
        ];

        $testCases["single expression multiple match operator {$matchOperator->name} not equal"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['/^test2$/', '/^test3$/']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => ['testing', 'test']],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $matchOperator->is(PolicyDefinitionMatchOperator::NOT_MATCHES_ALL),
        ];
    }

    foreach ($containsOperators as $containsOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $containsOperator);
        $testCases["single expression single value match operator {$containsOperator->name} true"] = function () use ($policyDefinition, $containsOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['test', 'test2', 'test3']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => ['test', 'test2']],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::CONTAINS_ALL),
            ];
        };
        $testCases["single expression multiple value match operator {$containsOperator->name} true"] = function () use ($policyDefinition, $containsOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['test', 'test2']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => ['test', 'test2']],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::CONTAINS_ALL),
            ];
        };
        $testCases["single expression single value match operator {$containsOperator->name} false"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['invalid']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => ['test', 'test2']],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL),
        ];
        $testCases["single expression multiple value match operator {$containsOperator->name} false"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['invalid', 'invalid2']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => ['test', 'test2']],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $containsOperator->is(PolicyDefinitionMatchOperator::NOT_CONTAINS_ALL),
        ];
    }

    foreach ($oneOfOperators as $oneOfOperator) {
        // Single expression test case
        $policyDefinition = PolicyDefinition::from(PolicyDefinitionType::EXPRESSION, 'testing', $oneOfOperator);
        $testCases["single expression match operator {$oneOfOperator->name} true"] = function () use ($policyDefinition, $oneOfOperator) {
            return [
                'policyDefinition' => [$policyDefinition],
                'values' => [PolicyValue::from($policyDefinition, Collection::make(['test', 'testing', 'testing2']))],
                'context' => [
                    'scopeType' => 'testing',
                    'scope' => ['testing' => ['testing', 'testing2']],
                    'appName' => $this->application->name,
                    'environment' => $this->environment->name,
                ],
                'expected' => $oneOfOperator->is(PolicyDefinitionMatchOperator::ONE_OF),
            ];
        };
        $testCases["single expression match operator {$oneOfOperator->name} false"] = fn () => [
            'policyDefinition' => [$policyDefinition],
            'values' => [PolicyValue::from($policyDefinition, Collection::make(['testing', 'test']))],
            'context' => [
                'scopeType' => 'testing',
                'scope' => ['testing' => ['not-test', 'not-test2']],
                'appName' => $this->application->name,
                'environment' => $this->environment->name,
            ],
            'expected' => $oneOfOperator->is(PolicyDefinitionMatchOperator::NOT_ONE_OF),
        ];
    }

    return $testCases;
}
