<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use BelongsToTeam;
    use HasUlids;

    protected $fillable = [
        'email',
        'role',
        'team_id',
        'user_id',
        'expires_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
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
