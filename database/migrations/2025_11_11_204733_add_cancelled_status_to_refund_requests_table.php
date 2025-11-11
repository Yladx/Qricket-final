<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'cancelled' status
        DB::statement("ALTER TABLE refund_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processed', 'completed', 'failed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'cancelled' from the enum
        DB::statement("ALTER TABLE refund_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processed', 'completed', 'failed') DEFAULT 'pending'");
    }
};
