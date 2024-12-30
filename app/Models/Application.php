<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUlids;
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'last_seen_at',
        'color',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_seen_at' => 'datetime',
        ];
    }

    public function environments(): Attribute
    {
        return Attribute::make(get: function () {
            $query = Environment::query()
                ->select('environments.*')
                ->from('feature_flags')
                ->join('application_feature_flag', 'feature_flags.id', '=', 'application_feature_flag.feature_flag_id')
                ->join('environment_feature_flag', 'feature_flags.id', '=', 'environment_feature_flag.feature_flag_id')
                ->join('environments', 'environments.id', '=', 'environment_feature_flag.environment_id')
                ->where('application_feature_flag.application_id', $this->id)
                ->groupBy(['environments.id', 'environments.name'])
                ->orderBy('environments.name');

            return $query
                ->get();
        });
    }
}
