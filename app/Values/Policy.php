<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Policy as PolicyModel;
use App\Values\Collections\PolicyCollection;
use App\Values\Collections\PolicyDefinitionCollection;
use App\Values\Factories\PolicyFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Values\Optional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, Optional|string $name, Optional|string $description, Optional|PolicyDefinitionCollection $definition, Carbon|null $createdAt = null, Carbon|null $updatedAt = null)
 * @method static PolicyCollection<Policy> collect(iterable $items)
 * @method static PolicyFactory<Policy> factory(Collection|array|int $data = [])
 */
#[Collection(PolicyCollection::class)]
#[Factory(PolicyFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Policy extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('policy')]
        public Optional|string $id,
        public Optional|string $name,
        #[Cast(CollectionOf::class, PolicyDefinition::class)]
        public Optional|PolicyDefinitionCollection $definition,
        public ?string $description = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
    ) {
    }

    #[Transforms(PolicyModel::class)]
    public static function fromModel(PolicyModel $policy): array
    {
        return [
            'id' => $policy->id,
            'name' => $policy->name,
            'description' => $policy->description,
            'definition' => PolicyDefinitionCollection::make($policy->definition),
            'created_at' => $policy->created_at,
            'updated_at' => $policy->updated_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required'],
            'definition' => ['nullable'],
        ];
    }
}
