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
        Schema::create('analytics_product_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_run_id')->constrained('analytics_runs')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->string('category_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('unit');
            $table->decimal('stock', 12, 2);
            $table->decimal('critical_stock', 12, 2);
            $table->decimal('stock_value', 14, 2);
            $table->decimal('average_daily_usage', 12, 4);
            $table->decimal('remaining_days', 12, 2)->nullable();
            $table->date('predicted_out_date')->nullable();
            $table->unsignedTinyInteger('risk_score');
            $table->string('risk_label');
            $table->decimal('recommended_quantity', 12, 2);
            $table->unsignedInteger('open_alerts_count')->default(0);
            $table->unsignedInteger('resolved_alerts_count')->default(0);
            $table->timestamp('latest_alert_at')->nullable();
            $table->timestamps();

            $table->index(['analytics_run_id', 'risk_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_product_kpis');
    }
};
