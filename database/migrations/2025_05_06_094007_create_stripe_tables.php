<?php

declare(strict_types=1);

use App\Models\StripePrice;
use App\Models\StripeProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('stripe_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
        });


        Schema::create('stripe_products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('stripe_id');
            $table->foreignIdFor(StripePrice::class, 'stripe_base_price_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(StripePrice::class, 'stripe_metered_price_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->boolean('active');
            $table->jsonb('entitlements');
            $table->jsonb('metadata');
            $table->integer('order');
            $table->timestamps();
        });

        Schema::table('stripe_prices', function (Blueprint $table) {
            $table->string('stripe_id');
            $table->foreignIdFor(StripeProduct::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('unit_amount')->nullable()->default(0);
            $table->boolean('active');
            $table->timestamps();
        });

        if (config('beacon.billing.enabled', false)) {
            \Artisan::call('stripe:sync');
        }
    }
};
