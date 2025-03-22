<?php

declare(strict_types=1);

namespace App\Models {

    use Illuminate\Database\Eloquent\Model;

    class Tenant extends Model
    {
    }
}

namespace {

    use App\Enums\Role;
    use App\Models\Team;
    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class () extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table('teams', function (Blueprint $table) {
                $table->string('tenant_id')->nullable();
            });

            $tenant = Tenant::create([
                'owner_id' => User::query()
                    ->oldest()
                    ->whereHas('roles', function (Builder $query) {
                        $query->where('name', Role::OWNER());
                    })->first()->id,
            ]);
            Team::query()->update(['tenant_id' => $tenant->id]);

            Schema::table('teams', function (Blueprint $table) {
                $table->string('tenant_id')->nullable(false)->change();
                $table->foreign('tenant_id')->cascadeOnDelete()->references('id')->on('tenants');
            });
        }
    };
}
