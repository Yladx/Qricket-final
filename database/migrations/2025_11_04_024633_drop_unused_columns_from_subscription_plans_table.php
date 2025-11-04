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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $columns = ['api_access', 'priority_support', 'custom_domain', 'white_label', 'max_event_duration_days'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('subscription_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_event_duration_days')->nullable()->after('tickets_per_event');
            $table->boolean('api_access')->default(false)->after('advanced_analytics');
            $table->boolean('priority_support')->default(false)->after('api_access');
            $table->boolean('custom_domain')->default(false)->after('priority_support');
            $table->boolean('white_label')->default(false)->after('custom_domain');
        });
    }
};
