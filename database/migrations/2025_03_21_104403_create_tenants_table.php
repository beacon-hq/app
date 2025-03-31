<?php

declare(strict_types=1);

namespace App\Models {

    use Illuminate\Database\Eloquent\Concerns\HasUlids;
    use Illuminate\Database\Eloquent\Model;

    if (!class_exists(Tenant::class)) {
        class Tenant extends Model
        {
            use HasUlids;
        }
    }
}

namespace {

    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class () extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('tenants', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignIdFor(User::class, 'owner_id');
                $table->timestamps();
            });

            Schema::create('tenant_user', function (Blueprint $table) {
                $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
                $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }
    };
}
