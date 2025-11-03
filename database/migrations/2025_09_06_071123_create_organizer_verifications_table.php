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
        Schema::create('organizer_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Personal Information Fields
            $table->string('full_name')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('nationality')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            
            // Family Information
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            
            // Additional Contact Information
            $table->string('alternate_email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Professional Information
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->text('professional_experience')->nullable();
            
            // Verification Status and Documents
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('document_type'); // id_card, passport, business_license, etc.
            $table->string('document_file_path'); // R2 storage path (legacy single)
            $table->string('document_front_path')->nullable();
            $table->string('document_back_path')->nullable();
            
            // Additional Verification Documents
            $table->string('additional_document_type')->nullable();
            $table->string('additional_document_path')->nullable();
            
            // Organization Information
            $table->string('organization_name')->nullable();
            $table->text('organization_description')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            
            // Terms and Review
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // admin who reviewed
            $table->timestamp('reviewed_at')->nullable();
            
            // Verification Notes
            $table->text('verification_notes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('admins')->onDelete('set null');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_verifications');
    }
};