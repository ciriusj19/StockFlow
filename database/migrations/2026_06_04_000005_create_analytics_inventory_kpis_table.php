<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_inventory_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_run_id')->constrained('analytics_runs')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->string('unit');
            $table->unsignedInteger('inventories_count')->default(0);
            $table->decimal('total_expected_quantity', 12, 2)->default(0);
            $table->decimal('total_difference', 12, 2)->default(0);
            $table->decimal('absolute_difference', 12, 2)->default(0);
            $table->decimal('positive_difference', 12, 2)->default(0);
            $table->decimal('negative_difference', 12, 2)->default(0);
            $table->decimal('reliability_score', 8, 2)->default(100);
            $table->date('last_inventory_date')->nullable();
            $table->timestamps();

            $table->index(['analytics_run_id', 'absolute_difference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_inventory_kpis');
    }
};
