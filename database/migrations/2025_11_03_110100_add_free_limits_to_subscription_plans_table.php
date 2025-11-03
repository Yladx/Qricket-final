<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'free_events_count')) {
                $table->unsignedInteger('free_events_count')->default(0)->after('price');
            }
            if (!Schema::hasColumn('subscription_plans', 'free_event_ticket_limit')) {
                $table->unsignedInteger('free_event_ticket_limit')->default(0)->after('free_events_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'free_events_count')) {
                $table->dropColumn('free_events_count');
            }
            if (Schema::hasColumn('subscription_plans', 'free_event_ticket_limit')) {
                $table->dropColumn('free_event_ticket_limit');
            }
        });
    }
};


