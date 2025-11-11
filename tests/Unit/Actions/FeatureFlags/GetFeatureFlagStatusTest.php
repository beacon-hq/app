<?php

declare(strict_types=1);

use App\Actions\AppContext\SetAppContext;
use App\Actions\AppContext\SetTeamContext;
use App\Actions\FeatureFlags\GetFeatureFlagStatus;
use App\Enums\Boolean;
use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Events\FeatureFlagEvaluatedEvent;
use App\Events\FeatureFlagMissedEvent;
use App\Facades\Murmur;
use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Models\FeatureType;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\FeatureFlag as FeatureFlagValue;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use App\Values\PolicyDefinition;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Lottery;

beforeEach(function () {
    [$this->team, , $organization] = createOwner();

    SetAppContext::run($organization, $this->team);

    // Create an application and environment
    $this->application = Application::factory()->for($this->team)->create([
        'name' => 'test-app',
        'display_name' => 'Test App',
    ]);

    $this->environment = Environment::factory()->for($this->team)->create([
        'name' => 'test-env',
        'description' => 'Test Environment',
    ]);

    // Create a feature flag
    $this->featureFlag = FeatureFlag::factory()
        ->create([
            'name' => 'test-flag',
            'description' => 'Test Feature Flag',
            'status' => true,
            'team_id' => $this->team->id,
            'feature_type_id' => FeatureType::factory()->for($this->team)->create()->id,
        ]);

    // Create context for testing
    $this->context = FeatureFlagContext::from(
        appName: 'test-app',
        environment: 'test-env',
        scopeType: 'array',
        scope: ['user_id' => '123', 'email' => 'test@example.com', 'role' => 'admin']
    );

    $this->getFeatureFlagStatusAction = GetFeatureFlagStatus::make();

    Event::fake();
    Bus::fake();
});

it('returns false for a non-existent feature flag', function () {
    Event::fake();

    $featureFlag = FeatureFlagValue::withoutValidation(['name' => 'non-existent-flag']);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull()
        ->and($response->featureFlag)->toBe('non-existent-flag');

    Event::assertDispatched(FeatureFlagMissedEvent::class, function ($event) use ($featureFlag) {
        return $event->featureFlag->name === $featureFlag->name &&
               $event->context === $this->context &&
               $event->response->active === false;
    });
});

it('returns inactive status when no specific status exists', function () {
    Event::fake();

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    SetTeamContext::run($this->team);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull()
        ->and($response->featureFlag)->toBe('test-flag');

    Event::assertDispatched(FeatureFlagEvaluatedEvent::class);
});

it('returns false when feature flag status is disabled', function () {
    // Create a disabled feature flag status
    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => false,
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();
});

it('evaluates a simple expression policy', function () {
    // Create a feature flag status with a simple policy
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'role',
                operator: PolicyDefinitionMatchOperator::EQUAL,
                values: ['admin']
            ),
        ]),
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeNull();

    // Now test with a context that doesn't match
    $context = FeatureFlagContext::from(
        appName: 'test-app',
        environment: 'test-env',
        scopeType: 'array',
        scope: ['user_id' => '123', 'email' => 'test@example.com', 'role' => 'user']
    );

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();
});

it('evaluates a complex policy with AND operator', function () {
    // Create a feature flag status with an AND policy
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'role',
                operator: PolicyDefinitionMatchOperator::EQUAL,
                values: ['admin']
            ),
            PolicyDefinition::from(
                type: PolicyDefinitionType::OPERATOR,
                subject: Boolean::AND->value,
                operator: null,
                values: []
            ),
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'email',
                operator: PolicyDefinitionMatchOperator::CONTAINS_ANY,
                values: ['example.com']
            ),
        ]),
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeNull();

    // Now test with a context that doesn't match one condition
    $context = FeatureFlagContext::from(
        appName: 'test-app',
        environment: 'test-env',
        scopeType: 'array',
        scope: ['user_id' => '123', 'email' => 'test@other.com', 'role' => 'admin']
    );

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();
});

it('evaluates a complex policy with OR operator', function () {
    // Create a feature flag status with an OR policy
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'definition' => PolicyDefinitionCollection::make([
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'role',
                operator: PolicyDefinitionMatchOperator::EQUAL,
                values: ['super-admin']
            ),
            PolicyDefinition::from(
                type: PolicyDefinitionType::OPERATOR,
                subject: Boolean::OR->value,
                operator: null,
                values: []
            ),
            PolicyDefinition::from(
                type: PolicyDefinitionType::EXPRESSION,
                subject: 'email',
                operator: PolicyDefinitionMatchOperator::CONTAINS_ANY,
                values: ['example.com']
            ),
        ]),
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeNull();

    // Now test with a context that doesn't match either condition
    $context = FeatureFlagContext::from(
        appName: 'test-app',
        environment: 'test-env',
        scopeType: 'array',
        scope: ['user_id' => '123', 'email' => 'test@other.com', 'role' => 'user']
    );

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();
});

it('evaluates rollout percentages with random strategy', function () {
    // Create a feature flag status with 100% rollout
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'rollout_percentage' => 100,
        'rollout_strategy' => 'random',
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);

    Lottery::alwaysWin();
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeNull();

    // Create a feature flag status with 0% rollout
    $status->update(['rollout_percentage' => 1]);

    Lottery::alwaysLose();
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();

    Lottery::determineResultsNormally();
});

it('evaluates context-based rollout strategy', function () {
    // Create a feature flag status with context-based rollout
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'rollout_percentage' => 99,
        'rollout_strategy' => 'context',
        'rollout_context' => ['user_id'],
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeNull();

    // Update to a lower percentage that should disable the flag
    $status->update(['rollout_percentage' => 1]);

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeFalse()
        ->and($response->value)->toBeNull();
});

it('evaluates variants with random strategy', function () {
    // Create a feature flag status with variants
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'variant_strategy' => 'random',
        'variants' => [
            [
                'name' => 'control',
                'value' => 'control-value',
                'type' => 'string',
                'percentage' => 50,
            ],
            [
                'name' => 'treatment',
                'value' => 'treatment-value',
                'type' => 'string',
                'percentage' => 50,
            ],
        ],
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBeIn(['control-value', 'treatment-value']);
});

it('evaluates variants with context strategy', function () {
    // Create a feature flag status with context-based variants
    FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'variant_strategy' => 'context',
        'variant_context' => ['user_id'],
        'variants' => [
            [
                'name' => 'correct',
                'value' => 'correct-value',
                'type' => 'string',
                'percentage' => 90,
            ],
            [
                'name' => 'incorrect',
                'value' => 'incorrect-value',
                'type' => 'string',
                'percentage' => 10,
            ],
        ],
    ]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);

    expect($response)->toBeInstanceOf(FeatureFlagResponse::class)
        ->and($response->active)->toBeTrue()
        ->and($response->value)->toBe('correct-value');
});

it('converts variant values based on their type', function () {
    // Create a feature flag status with variants of different types
    $status = FeatureFlagStatus::factory()->create([
        'feature_flag_id' => $this->featureFlag->id,
        'application_id' => $this->application->id,
        'environment_id' => $this->environment->id,
        'status' => true,
        'variant_strategy' => 'context',
        'variant_context' => ['user_id'],
        'variants' => [
            [
                'name' => 'string',
                'value' => 'string-value',
                'type' => 'string',
                'percentage' => 25,
            ],
            [
                'name' => 'integer',
                'value' => '42',
                'type' => 'integer',
                'percentage' => 25,
            ],
            [
                'name' => 'float',
                'value' => '3.14',
                'type' => 'float',
                'percentage' => 25,
            ],
            [
                'name' => 'json',
                'value' => '{"key": "value"}',
                'type' => 'json',
                'percentage' => 25,
            ],
        ],
    ]);

    Murmur::expects('hash3_int')->times(4)->andReturnValues([10, 30, 60, 90]);

    $featureFlag = FeatureFlagValue::from($this->featureFlag);
    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);
    expect($response->value)->toBe('string-value')
        ->and($response->value)->toBeString();

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);
    expect($response->value)->toBe(42)
        ->and($response->value)->toBeInt();

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);
    expect($response->value)->toBe(3.14)
        ->and($response->value)->toBeFloat();

    $response = $this->getFeatureFlagStatusAction->handle($featureFlag, $this->context);
    expect($response->value)->toBe(['key' => 'value'])
        ->and($response->value)->toBeArray();
});
