<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->boolean('weekend');
            $table->decimal('price', 8, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['region_id', 'package_type_id', 'weekend']);
        });

        // Partial unique index (rather than Blueprint::unique()) so a soft-deleted rate
        // doesn't block re-creating the same carrier/package type/region combination.
        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX shipping_rates_carrier_package_type_region_unique
            ON shipping_rates (carrier_id, package_type_id, region_id)
            WHERE deleted_at IS NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
