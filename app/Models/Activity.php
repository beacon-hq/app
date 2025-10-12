<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\AppContext\GetAppContext;
use App\Models\Traits\BelongsToOrganization;
use App\Models\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    use BelongsToOrganization;
    use BelongsToTeam;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $activity) {
            $appContext = GetAppContext::run();
            $activity->organization_id = $appContext['organization']->id;
            $activity->team_id = $appContext['team']->id;
        });
    }
}
