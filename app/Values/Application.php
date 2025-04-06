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
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $displayName, string|null $description, EnvironmentCollection|Optional $environments, Color|string $color = '#e3e3e3', Carbon|null $lastSeenAt = null)
 * @method static ApplicationCollection<Application> collect(iterable $items)
 * @method static ApplicationFactory<Application> factory(Collection|array|int $data = [])
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
        #[FromRouteParameter('application')]
        public Optional|string $id,
        public Optional|string $name,
        public Optional|string $displayName,
        public string|null $description,
        #[Cast(CollectionOf::class, Environment::class)]
        public EnvironmentCollection|Optional $environments,
        #[Cast(ColorCast::class)]
        public Color|string $color = '#e3e3e3',
        public Carbon|null $lastSeenAt = null,
    ) {
    }

    #[Transforms(ApplicationModel::class)]
    public static function fromModel(ApplicationModel $application): array
    {
        return [
            'id' => $application->id,
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
            'name' => [new OptionalOr(['required_without:id', 'exclude_with:id'])],
            'description' => [new OptionalOr(['nullable'])],
            'color' => [new OptionalOr(['present'])],
        ];
    }
}
