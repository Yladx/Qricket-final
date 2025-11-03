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
        // Fix any users who have verified_organizer but organizer is false
        DB::table('users')
            ->whereNotNull('verified_organizer')
            ->where('organizer', false)
            ->update(['organizer' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we can't determine
        // which users should have organizer=false
    }
};
