<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 *
 *
 * @property int $id
 * @property string $tokenable_type
 * @property string $tokenable_id
 * @property string $name
 * @property string $token
 * @property array<array-key, mixed>|null $abilities
 * @property Carbon|null $last_used_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $plain_text_suffix
 * @property-read Model|\Eloquent $tokenable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereAbilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken wherePlainTextSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereTokenableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereTokenableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AccessToken extends SanctumPersonalAccessToken
{
    use BelongsToTeam;

    protected $fillable = [
        'name',
        'token',
        'token_value',
        'plain_text_suffix',
        'abilities',
        'expires_at',
    ];

    protected $hidden = [
        'token_value',
        'token',
    ];

    public function teams(): MorphTo
    {
        return $this->tokenable();
    }

    protected function casts(): array
    {
        return [
            'token_value' => 'encrypted',
        ];
    }
}
