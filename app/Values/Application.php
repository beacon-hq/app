<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Application as ApplicationModel;
use App\Values\Collections\ApplicationCollection;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Factories\ApplicationFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $slug = null, ?string $name = null, ?string $display_name = null, ?string $description = null, ?string $last_seen_at = null, ?string $color = null, ?EnvironmentCollection<Environment> $environments = null)
 */
#[Collection(ApplicationCollection::class)]
#[Factory(ApplicationFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Application extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $displayName = null,
        public ?string $description = null,
        public ?string $lastSeenAt = null,
        public ?string $color = null,
        #[Cast(CollectionOf::class, Environment::class)]
        public ?EnvironmentCollection $environments = null,
    ) {
    }

    #[Transforms(ApplicationModel::class)]
    public static function fromModel(ApplicationModel $application): array
    {
        return [
            'slug' => $application->slug,
            'name' => $application->name,
            'display_name' => $application->display_name,
            'description' => $application->description,
            'last_seen_at' => $application->last_seen_at,
            'color' => $application->color,
            'environments' => $application->environments,
        ];
    }
}
