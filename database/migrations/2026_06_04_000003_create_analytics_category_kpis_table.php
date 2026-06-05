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
        Schema::create('analytics_category_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_run_id')->constrained('analytics_runs')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category_name');
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('critical_products_count')->default(0);
            $table->unsignedInteger('open_alerts_count')->default(0);
            $table->decimal('average_risk_score', 8, 2)->default(0);
            $table->decimal('stock_value', 14, 2)->default(0);
            $table->decimal('recommended_quantity_total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['analytics_run_id', 'average_risk_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_category_kpis');
    }
};
