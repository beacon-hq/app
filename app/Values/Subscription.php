<?php

declare(strict_types=1);

namespace App\Values;

use App;
use App\Services\SubscriptionBillingService;
use App\Values\Factories\SubscriptionFactory;
use Bag\Attributes\Factory;
use Bag\Attributes\Hidden;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(string $id, string $stripeId, string $status, Product $plan, ?string $trialEndsAt = null, ?string $endsAt = null, ?string $createdAt = null)
 * @method static SubscriptionFactory<Subscription> factory(Collection|array|int $data = [])
 */
#[Factory(SubscriptionFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Subscription extends Bag
{
    use HasFactory;

    public function __construct(
        public string $id,
        #[Hidden]
        public string $stripeId,
        public string $status,
        public Product $plan,
        public ?string $trialEndsAt = null,
        public ?string $endsAt = null,
        public ?string $createdAt = null,
    ) {
    }

    #[Transforms(\Laravel\Cashier\Subscription::class)]
    public static function fromModel(\Laravel\Cashier\Subscription $subscription): array
    {
        $subscriptionBillingService = app(SubscriptionBillingService::class);

        return [
            'id' => $subscription->id,
            'stripe_id' => $subscription->stripe_id,
            'status' => $subscription->stripe_status,
            'plan' => $subscriptionBillingService->getPlan(App::context()->organization),
            'trial_ends_at' => $subscription->trial_ends_at,
            'ends_at' => $subscription->ends_at,
            'created_at' => $subscription->created_at,
        ];
    }
}
