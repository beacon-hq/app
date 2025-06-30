<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Activity;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use App\Values\Collections\ActivityLogCollection;
use App\Values\Factories\ActivityLogFactory;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Facades\URL;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[Collection(ActivityLogCollection::class)]
#[Factory(ActivityLogFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class ActivityLog extends Bag
{
    use HasFactory;

    public function __construct(
        public int $id,
        public User $user,
        public string $event,
        public LaravelCollection $properties,
        public CarbonImmutable $createdAt,
        public string $subject,
    ) {
    }

    #[Transforms(Activity::class)]
    public static function fromModel(Activity $model): array
    {

        return [
            'id' => $model->id,
            'user' => $model->causer !== null ? User::from($model->causer) : User::from(name: 'System', email: 'system@beacon-hq.dev', avatar: URL::asset('/images/system-avatar.svg')),
            'event' => $model->event,
            'properties' => LaravelCollection::make($model->properties),
            'createdAt' => CarbonImmutable::parse($model->created_at),
            'subject' => self::getSubject($model),
        ];
    }

    protected static function getSubject(Activity $model): string
    {
        return match ($model->subject_type) {
            FeatureFlag::class => '',
            FeatureFlagStatus::class => $model->subject->application->name . ' > ' . $model->subject->environment->name,
            default => \class_basename($model->subject_type) . ': ' . ($model->subject->name ?? $model->subject->id),
        };
    }
}
