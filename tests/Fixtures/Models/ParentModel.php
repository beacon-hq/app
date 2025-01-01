<?php

declare(strict_types=1);

namespace Tests\Fixtures\Models;

use App\Models\Tenant;
use App\Models\Traits\DeferRouteBinding;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class ParentModel extends Model
{
    use HasUlids;
    use HasApiTokens;
    use DeferRouteBinding;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(RegularModel::class, 'tenant_user', 'tenant_id', 'user_id')->withTimestamps();
    }

    public function lazies()
    {
        return $this->belongsToMany(LazilyResolved::class, 'tenant_user', 'tenant_id', 'user_id')->withTimestamps();
    }

    public static function factory()
    {
        return Factory::factoryForModel(Tenant::class);
    }
}
