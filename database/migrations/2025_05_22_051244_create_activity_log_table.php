<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Organization::class);
            $table->foreignIdFor(Team::class);
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableUlidMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'team_id', 'log_name']);
            $table->index(['team_id', 'log_name']);
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
