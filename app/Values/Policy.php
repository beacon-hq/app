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
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Casts\CollectionOf;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string $slug = null, ?string $name = null, ?string $description = null, ?string $id = null, ?PolicyDefinitionCollection<PolicyDefinition> $definition = null, ?Carbon $createdAt = null, ?Carbon $updatedAt = null)
 */
#[Collection(PolicyCollection::class)]
#[Factory(PolicyFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Policy extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $slug = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $id = null,
        #[Cast(CollectionOf::class, PolicyDefinition::class)]
        public ?PolicyDefinitionCollection $definition = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    #[Transforms(PolicyModel::class)]
    public static function fromModel(PolicyModel $policy): array
    {
        return [
            'slug' => $policy->slug,
            'name' => $policy->name,
            'description' => $policy->description,
            'id' => $policy->id,
            'definition' => $policy->definition,
            'created_at' => $policy->created_at,
            'updated_at' => $policy->updated_at,
        ];
    }
}
