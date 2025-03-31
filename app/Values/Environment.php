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
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $name = null, ?string $description = null, ?string $slug = null, string|Color $color = '#e3e3e3')
 * @method static EnvironmentCollection collect(iterable $items)
 */
#[Collection(EnvironmentCollection::class)]
#[Factory(EnvironmentFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Environment extends Bag
{
    use HasFactory;

    public function __construct(
        #[HiddenFromTypeScript]
        public ?string $id = null,
        #[FromRouteParameter]
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $description = null,
        #[Cast(ColorCast::class)]
        public string|Color $color = '#e3e3e3',
        public ?Carbon $lastSeenAt = null,
    ) {
    }

    #[Transforms(EnvironmentModel::class)]
    public static function fromModel(EnvironmentModel $environment): array
    {
        return [
            'id' => $environment->id,
            'name' => $environment->name,
            'description' => $environment->description,
            'slug' => $environment->slug,
            'color' => $environment->color,
            'lastSeenAt' => $environment->last_seen_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required_without:slug', 'exclude_with:slug'],
            'description' => ['nullable'],
            'color' => ['present'],
        ];
    }
}
