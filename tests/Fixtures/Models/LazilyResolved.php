<?php

declare(strict_types=1);

namespace Tests\Fixtures\Models;

use App\Models\Traits\DeferRouteBinding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LazilyResolved extends Model
{
    use DeferRouteBinding;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = ['first_name', 'last_name', 'email'];

    public static function factory()
    {
        return Factory::factoryForModel(User::class);
    }
}
