<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Color;
use App\Models\Environment as EnvironmentModel;
use App\Values\Casts\Color as ColorCast;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Factories\EnvironmentFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $description, Color|string $color = '#e3e3e3', Carbon|null $lastSeenAt = null)
 * @method static EnvironmentCollection<Environment> collect(iterable $items)
 * @method static EnvironmentFactory<Environment> factory(Collection|array|int $data = [])
 */
#[Collection(EnvironmentCollection::class)]
#[Factory(EnvironmentFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Environment extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('environment')]
        public Optional|string $id,
        public Optional|string $name,
        public Optional|string $description,
        #[Cast(ColorCast::class)]
        public Color|string $color = '#e3e3e3',
        public CarbonImmutable|null $lastSeenAt = null,
    ) {
    }

    #[Transforms(EnvironmentModel::class)]
    public static function fromModel(EnvironmentModel $environment): array
    {
        return [
            'id' => $environment->id,
            'name' => $environment->name,
            'description' => $environment->description,
            'color' => $environment->color,
            'lastSeenAt' => $environment->last_seen_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:id', 'exclude_with:id'],
            'color' => ['present'],
        ];
    }
}
