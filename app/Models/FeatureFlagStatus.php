<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivot\FeatureFlagStatusPolicy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeatureFlagStatus extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'application_id',
        'environment_id',
        'feature_flag_id',
        'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class)
            ->using(FeatureFlagStatusPolicy::class)
            ->withPivot('order', 'values')
            ->orderByPivot('order', 'asc');
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'boolean',
        ];
    }
}
