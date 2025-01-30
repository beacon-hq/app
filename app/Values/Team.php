<?php

declare(strict_types=1);

namespace App\Values;

use App\Models\Team as TeamModel;
use App\Values\Factories\TeamFactory;
use Bag\Attributes\Factory;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;

/**
 * @method static static from(?string $id = null, ?string $name = null)
 */
#[Factory(TeamFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
readonly class Team extends Bag
{
    use HasFactory;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
    ) {
    }

    #[Transforms(TeamModel::class)]
    public static function fromModel(TeamModel $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
        ];
    }
}
