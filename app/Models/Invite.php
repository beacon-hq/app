<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use BelongsToTeam;
    use HasUlids;

    protected $fillable = [
        'email',
        'role',
        'team_id',
        'organization_id',
        'user_id',
        'expires_at',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'expires_at' => 'datetime',
        ];
    }
}
