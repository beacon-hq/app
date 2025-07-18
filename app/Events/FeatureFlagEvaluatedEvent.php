<?php

declare(strict_types=1);

namespace App\Events;

use App;
use App\Values\AppContext;
use App\Values\FeatureFlag;
use App\Values\FeatureFlagContext;
use App\Values\FeatureFlagResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static PendingDispatch dispatch(FeatureFlag $featureFlag, FeatureFlagContext $context, FeatureFlagResponse $response)
 */
class FeatureFlagEvaluatedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public AppContext $appContext;

    public function __construct(
        public readonly FeatureFlag $featureFlag,
        public readonly FeatureFlagContext $context,
        public readonly FeatureFlagResponse $response,
    ) {
        $this->appContext = App::context();
    }
}
