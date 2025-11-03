<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrganizerVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'document_type',
        'document_file_path',
        'document_front_path',
        'document_back_path',
        'organization_name',
        'organization_description',
        'contact_phone',
        'website',
        'terms_accepted',
        'terms_accepted_at',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        // Personal Information
        'full_name',
        'birthdate',
        'nationality',
        'address',
        'city',
        'state_province',
        'postal_code',
        'country',
        'father_name',
        'mother_name',
        'alternate_email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'occupation',
        'employer',
        'professional_experience',
        'additional_document_type',
        'additional_document_path',
        'verification_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'birthdate' => 'date',
    ];

    /**
     * Get the user that owns the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the verification.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Check if verification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if verification is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the document is a PDF.
     */
    public function isPdf(): bool
    {
        return strtolower(pathinfo($this->document_file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Check if the document is an image.
     */
    public function isImage(): bool
    {
        $extension = strtolower(pathinfo($this->document_file_path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png']);
    }

    /**
     * Check if the document type requires both front and back images.
     */
    public function requiresBothSides(): bool
    {
        return in_array($this->document_type, ['id_card', 'business_license', 'school_id']);
    }

    /**
     * Check if the document type only requires front image.
     */
    public function requiresFrontOnly(): bool
    {
        return in_array($this->document_type, ['passport', 'drivers_license', 'birth_certificate']);
    }

    /**
     * Get the document URL from R2 storage.
     */
    public function getDocumentUrlAttribute(): string
    {
        try {
            $disk = Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($this->document_file_path)) {
                \Log::warning("Document file not found: {$this->document_file_path}");
                return '#';
            }
            
            // Try to get signed URL first (for private files)
            try {
                return $disk->temporaryUrl($this->document_file_path, now()->addMinutes(60));
            } catch (\Exception $e) {
                \Log::info("Signed URL failed for {$this->document_file_path}, trying public URL");
                
                // Fallback to public URL
                $publicUrl = config('filesystems.disks.r2.url') . '/' . $this->document_file_path;
                return $publicUrl;
            }
        } catch (\Exception $e) {
            \Log::error("Error generating document URL for {$this->document_file_path}: " . $e->getMessage());
            return '#';
        }
    }

    /**
     * Get the front document URL from R2 storage.
     */
    public function getDocumentFrontUrlAttribute(): string
    {
        if (!$this->document_front_path) {
            return '#';
        }
        
        try {
            $disk = Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($this->document_front_path)) {
                \Log::warning("Front document file not found: {$this->document_front_path}");
                return '#';
            }
            
            // Try to get signed URL first (for private files)
            try {
                return $disk->temporaryUrl($this->document_front_path, now()->addMinutes(60));
            } catch (\Exception $e) {
                \Log::info("Signed URL failed for {$this->document_front_path}, trying public URL");
                
                // Fallback to public URL
                $publicUrl = config('filesystems.disks.r2.url') . '/' . $this->document_front_path;
                return $publicUrl;
            }
        } catch (\Exception $e) {
            \Log::error("Error generating front document URL for {$this->document_front_path}: " . $e->getMessage());
            return '#';
        }
    }

    /**
     * Get the back document URL from R2 storage.
     */
    public function getDocumentBackUrlAttribute(): string
    {
        if (!$this->document_back_path) {
            return '#';
        }
        
        try {
            $disk = Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($this->document_back_path)) {
                \Log::warning("Back document file not found: {$this->document_back_path}");
                return '#';
            }
            
            // Try to get signed URL first (for private files)
            try {
                return $disk->temporaryUrl($this->document_back_path, now()->addMinutes(60));
            } catch (\Exception $e) {
                \Log::info("Signed URL failed for {$this->document_back_path}, trying public URL");
                
                // Fallback to public URL
                $publicUrl = config('filesystems.disks.r2.url') . '/' . $this->document_back_path;
                return $publicUrl;
            }
        } catch (\Exception $e) {
            \Log::error("Error generating back document URL for {$this->document_back_path}: " . $e->getMessage());
            return '#';
        }
    }

    /**
     * Get a direct download URL for the document.
     */
    public function getDownloadUrlAttribute(): string
    {
        try {
            $disk = Storage::disk('r2');
            
            if (!$disk->exists($this->document_file_path)) {
                return '#';
            }
            
            // Generate a signed URL for download with appropriate headers
            return $disk->temporaryUrl($this->document_file_path, now()->addHour(), [
                'ResponseContentDisposition' => 'attachment; filename="' . basename($this->document_file_path) . '"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Failed to generate download URL: " . $e->getMessage());
            return $this->document_url;
        }
    }

    /**
     * Scope for pending verifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved verifications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected verifications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}