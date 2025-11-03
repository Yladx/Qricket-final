<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('platform_fee', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->string('currency', 8)->default('PHP');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'queued', 'processing', 'paid', 'failed'])->default('pending');
            $table->string('xendit_disbursement_id')->nullable();
            $table->string('destination_bank_code')->nullable();
            $table->string('destination_account_number')->nullable();
            $table->string('destination_account_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payouts');
    }
};

