<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); // notif id
            $table->unsignedBigInteger('user_id')->nullable()->index(); // target user (owner of notif) - null for admin notifications
            $table->unsignedBigInteger('admin_id')->nullable(); // admin who triggered the notification

            // type of notification context
            $table->enum('type', ['organizer', 'ticket', 'event', 'support'])->index();

            // action taken within the context
            $table->enum('action', [
                'reply',
                'verified',
                'accept',
                'reject',
                'news',
                // ticket sharing actions
                'share_request',
                'share_accepted',
                'share_declined',
                // event actions
                'event_ended',
                'event_ended_saved',
                'feedback_request',
                'staff_added',
                'admin_replied',
                'refund_completed',
                'attended',
                'refund_rejected',
                'payout_processed',
            ])->index();

            // reference id depending on type (e.g., ticket_id, user_id for organizer, event_id)
            $table->unsignedBigInteger('reference_id')->nullable()->index();

            // optional extra payload (message, small meta)
            $table->string('title')->nullable();
            $table->string('message', 512)->nullable();
            $table->json('data')->nullable();

            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'admin_id']); // Index for admin notification queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};


