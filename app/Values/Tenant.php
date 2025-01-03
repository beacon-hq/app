<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Tenant as TenantModel;
use App\Values\Factories\TenantFactory;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;

/**
 * @method static static from(string $name, ?string $id = null)
 */
#[Factory(TenantFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class Tenant extends Bag
{
    use HasFactory;

    public function __construct(
        public string $name,
        public ?string $id = null,
    ) {
    }

    #[Transforms(TenantModel::class)]
    public static function fromModel(TenantModel $tenant): array
    {
        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
        ];
    }
}
