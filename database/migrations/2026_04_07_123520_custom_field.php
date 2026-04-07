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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['select', 'string']);
            $table->timestamps();
        });

        Schema::create('custom_field_stock_parents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('stock_parent_id')->constrained('stock_parent')->cascadeOnDelete();
            $table->string('value')->default('-');
            $table->timestamps();
            $table->index(['custom_field_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('custom_field_stock_parents');
    }
};
