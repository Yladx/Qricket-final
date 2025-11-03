<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop columns safely, handling foreign keys/indexes if present
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });

        // approved_by may have a foreign key/index - drop FK first if it exists
        if (Schema::hasColumn('events', 'approved_by')) {
            try {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropForeign(['approved_by']);
                });
            } catch (\Throwable $e) {
                // FK might already be gone or not named conventionally; ignore
            }
            try {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropIndex(['approved_by']);
                });
            } catch (\Throwable $e) {
                // Index may not exist; ignore
            }
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('approved_by');
            });
        }

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'approval_notes')) {
                $table->dropColumn('approval_notes');
            }
            if (Schema::hasColumn('events', 'submitted_for_approval_at')) {
                $table->dropColumn('submitted_for_approval_at');
            }
            if (Schema::hasColumn('events', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('events', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('events', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('events', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('events', 'submitted_for_approval_at')) {
                $table->timestamp('submitted_for_approval_at')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('events', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('submitted_for_approval_at');
            }
            if (!Schema::hasColumn('events', 'rejection_reason')) {
                $table->string('rejection_reason', 1000)->nullable()->after('rejected_at');
            }
        });
    }
};


