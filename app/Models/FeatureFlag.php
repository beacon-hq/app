<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasSlug;
use App\Models\Traits\Lazily;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeatureFlag extends Model
{
    use HasUlids;
    use HasSlug;
    use HasFactory;
    use BelongsToTenant;
    use Lazily;

    protected $fillable = [
        'name',
        'description',
        'last_seen_at',
        'feature_type_id',
        'tenant_id',
    ];

    public function featureType(): BelongsTo
    {
        return $this->belongsTo(FeatureType::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class)
            ->using(FeatureFlagPolicy::class)
            ->withPivot('order', 'values')
            ->orderByPivot('order', 'asc');
    }

    public function environments(): BelongsToMany
    {
        return $this->belongsToMany(Environment::class);
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_seen_at' => 'datetime',
        ];
    }
}
