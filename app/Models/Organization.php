<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\CurrentOrganizationScope;
use App\Models\Scopes\CurrentTeamScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use function Illuminate\Events\queueable;
use Laravel\Cashier\Billable;

class Organization extends Model
{
    use Billable;
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'owner_id',
        'name',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->withoutGlobalScopes([
            CurrentOrganizationScope::class, CurrentTeamScope::class,
        ]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function stripeName()
    {
        return $this->name;
    }

    public function stripeEmail(): string
    {
        return $this->owner->email;
    }

    protected static function booted()
    {
        static::updated(queueable(function (Organization $organization) {
            if ($organization->hasStripeId()) {
                $organization->syncStripeCustomerDetails();
            }
        }));
    }
}
