<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Color;
use App\Models\Application as ApplicationModel;
use App\Values\Casts\Color as ColorCast;
use App\Values\Collections\ApplicationCollection;
use App\Values\Collections\EnvironmentCollection;
use App\Values\Factories\ApplicationFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $slug = null, ?string $name = null, ?string $display_name = null, ?string $description = null, ?string $last_seen_at = null, string|Color $color = '#e3e3e3', ?EnvironmentCollection $environments = null)
 * @method static ApplicationCollection collect(iterable $items)
 */
#[Collection(ApplicationCollection::class)]
#[Factory(ApplicationFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class Application extends Bag
{
    use HasFactory;

    public function __construct(
        #[HiddenFromTypeScript]
        public ?string $id = null,
        #[FromRouteParameter]
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $displayName = null,
        public ?string $description = null,
        public ?string $lastSeenAt = null,
        #[Cast(ColorCast::class)]
        public string|Color $color = '#e3e3e3',
        #[Cast(CollectionOf::class, Environment::class)]
        public ?EnvironmentCollection $environments = null,
    ) {
    }

    #[Transforms(ApplicationModel::class)]
    public static function fromModel(ApplicationModel $application): array
    {
        return [
            'id' => $application->id,
            'slug' => $application->slug,
            'name' => $application->name,
            'display_name' => $application->display_name,
            'description' => $application->description,
            'last_seen_at' => $application->last_seen_at,
            'color' => $application->color,
            'environments' => $application->environments,
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

    public function toJson($options = 0): string|false
    {
        return json_encode($this->toCollection()->except('id')->toArray(), JSON_THROW_ON_ERROR | $options);
    }
}
