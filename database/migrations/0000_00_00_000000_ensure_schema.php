<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MLL\LaravelUtils\Database\ConditionalMigration;

return new class () extends Migration implements ConditionalMigration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = explode(',', str_replace(', ', ',', config('database.connections.pgsql.search_path')))[0];
        if (!DB::selectOne('SELECT EXISTS(SELECT 1 FROM pg_namespace WHERE nspname = :schema)', ['schema' => $schema])?->exists ?? true) {
            DB::statement('ALTER SCHEMA public RENAME TO '.$schema);
        }
    }

    public function shouldRun(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }
};
