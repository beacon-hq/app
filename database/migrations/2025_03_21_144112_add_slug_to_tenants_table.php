<?php

declare(strict_types=1);

namespace App\Models {

    use Illuminate\Database\Eloquent\Model;

    class Tenant extends Model
    {
    }
}

namespace {

    use App\Models\Tenant;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class () extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('slug')->nullable();
            });

            Tenant::all()->each(fn (Tenant $tenant) => $tenant->update(['slug' => \Str::slug($tenant->name)]));

            Schema::table('tenants', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique(['owner_id', 'slug']);
            });
        }
    };
}
