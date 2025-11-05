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
        Schema::table('support_tickets', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['assigned_to']);
            // Drop the column
            $table->dropColumn('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Re-add the column
            $table->unsignedBigInteger('assigned_to')->nullable()->after('priority');
            // Re-add the foreign key
            $table->foreign('assigned_to')->references('id')->on('admins')->onDelete('set null');
        });
    }
};
