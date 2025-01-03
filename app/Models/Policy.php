<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUlids;
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'definition',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'definition' => 'array',
        ];
    }
}
