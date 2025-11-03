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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Basic, Pro, Enterprise
            $table->string('slug')->unique(); // free, basic, pro, enterprise
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Monthly price
            $table->string('currency', 3)->default('USD');
            $table->integer('events_per_month')->nullable(); // null = unlimited
            $table->integer('tickets_per_event')->nullable(); // null = unlimited
            $table->integer('max_event_duration_days')->nullable(); // null = unlimited
            $table->boolean('advanced_layouts')->default(false);
            $table->boolean('advanced_analytics')->default(false);
            $table->boolean('custom_domain')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('white_label')->default(false);
            $table->json('features')->nullable(); // Additional features as JSON
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
