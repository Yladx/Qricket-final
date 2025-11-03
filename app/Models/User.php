<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const STATUS_ACTIVE = 'active';
    const STATUS_DELETED = 'deleted';
    const STATUS_BANNED = 'banned';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'organizer',
        'verified_organizer',
        'profile_picture',
        'status',
        'deleted_at',
            'banned_at',
            'banned_until',
        'banned_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Normalize first name capitalization.
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = is_string($value)
            ? ucfirst(mb_strtolower(trim($value)))
            : $value;
    }

    /**
     * Normalize last name capitalization.
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = is_string($value)
            ? ucfirst(mb_strtolower(trim($value)))
            : $value;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_organizer' => 'datetime',
            'password' => 'hashed',
            'organizer' => 'boolean',
            'deleted_at' => 'datetime',
            'banned_at' => 'datetime',
            'banned_until' => 'datetime',
        ];
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get only deleted users
     */
    public function scopeDeleted($query)
    {
        return $query->where('status', self::STATUS_DELETED)
            ->whereNotNull('deleted_at');
    }

    /**
     * Scope to get only banned users
     */
    public function scopeBanned($query)
    {
        return $query->where('status', self::STATUS_BANNED)
            ->whereNotNull('banned_at');
    }

    /**
     * Soft delete user (sets status to deleted)
     */
    public function softDelete()
    {
        $this->update([
            'status' => self::STATUS_DELETED,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Ban user (sets status to banned)
     */
    public function ban($reason = null, $until = null)
    {
        $this->update([
            'status' => self::STATUS_BANNED,
            'banned_at' => now(),
            'banned_until' => $until ? \Carbon\Carbon::parse($until) : null,
            'banned_reason' => $reason,
        ]);
    }

    /**
     * Unban user (sets status to active)
     */
    public function unban()
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'banned_at' => null,
            'banned_reason' => null,
        ]);
    }

    /**
     * Restore deleted user (sets status to active)
     */
    public function restore()
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'deleted_at' => null,
        ]);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is deleted
     */
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED && $this->deleted_at !== null;
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return $this->status === self::STATUS_BANNED && $this->banned_at !== null;
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user's profile picture URL.
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        if (!$this->profile_picture) {
            return null;
        }
        
        try {
            $disk = \Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($this->profile_picture)) {
                \Log::warning("Profile picture file not found in R2: " . $this->profile_picture);
                return null;
            }
            
            // Try to generate a signed URL first
            try {
                return $disk->temporaryUrl($this->profile_picture, now()->addHour());
            } catch (\Exception $e) {
                \Log::info("Profile picture signed URL failed, using regular URL: " . $e->getMessage());
                
                // Fallback to regular URL
                $url = $disk->url($this->profile_picture);
                
                // Ensure the URL is properly formatted
                if (strpos($url, 'http') !== 0) {
                    $url = 'https://' . ltrim($url, '/');
                }
                
                return $url;
            }
            
        } catch (\Exception $e) {
            \Log::error('Profile picture URL generation failed: ' . $e->getMessage(), [
                'user_id' => $this->id,
                'profile_picture' => $this->profile_picture
            ]);
            return null;
        }
    }

    /**
     * Check if the user is a verified organizer.
     */
    public function getIsOrganizerVerifiedAttribute(): bool
    {
        return !is_null($this->verified_organizer);
    }

    /**
     * Delete the user's profile picture from storage.
     */
    public function deleteProfilePicture(): bool
    {
        if (!$this->profile_picture) {
            return true; // No picture to delete
        }

        try {
            if (\Storage::disk('r2')->exists($this->profile_picture)) {
                \Storage::disk('r2')->delete($this->profile_picture);
                \Log::info('Profile picture deleted successfully', [
                    'user_id' => $this->id,
                    'file_path' => $this->profile_picture
                ]);
            }
            
            // Clear the profile_picture field
            $this->profile_picture = null;
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete profile picture: ' . $e->getMessage(), [
                'user_id' => $this->id,
                'file_path' => $this->profile_picture
            ]);
            return false;
        }
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the organizer verification requests for the user.
     */
    public function organizerVerifications(): HasMany
    {
        return $this->hasMany(OrganizerVerification::class);
    }

    /**
     * Get the events saved by the user.
     */
    public function savedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'saved_events')
                    ->withTimestamps()
                    ->orderBy('saved_events.created_at', 'desc');
    }

    // Removed organizedEvents relationship - using event_staff only

    /**
     * Events where the user is assigned as staff.
     */
    public function staffedEvents(): HasMany
    {
        return $this->hasMany(EventStaff::class, 'user_id');
    }

    /**
     * Get events organized by the user (where user is the main creator).
     */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    /**
     * Get events where user is staff or co-organizer.
     */
    public function staffEvents(): HasMany
    {
        return $this->hasMany(EventStaff::class, 'user_id');
    }

    /**
     * Get social media links for the user.
     */
    public function socialMediaLinks(): HasMany
    {
        return $this->hasMany(SocialMediaLink::class);
    }

    /**
     * Get a specific social media link by platform.
     */
    public function getSocialMediaLink($platform): ?SocialMediaLink
    {
        return $this->socialMediaLinks()->where('platform', $platform)->first();
    }

    /**
     * Log an activity for the user.
     */
    public function logActivity(string $action, ?string $description = null, ?array $metadata = null): ActivityLog
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if the user is an organizer.
     */
    public function isOrganizer(): bool
    {
        return $this->organizer;
    }

    /**
     * Check if the user is a verified organizer.
     */
    public function isVerifiedOrganizer(): bool
    {
        return $this->organizer && $this->verified_organizer !== null;
    }

    /**
     * Verify the user as an organizer.
     */
    public function verifyAsOrganizer(): bool
    {
        $this->organizer = true;
        $this->verified_organizer = now();
        $this->save();
        
        return true;
    }

    /**
     * Unverify the user as an organizer.
     */
    public function unverifyAsOrganizer(): bool
    {
        $this->verified_organizer = null;
        $this->save();
        
        return true;
    }

    /**
     * Toggle the organizer status.
     */
    public function toggleOrganizer(): bool
    {
        $this->organizer = !$this->organizer;
        if (!$this->organizer) {
            $this->verified_organizer = null;
        }
        $this->save();
        
        return $this->organizer;
    }

    /**
     * Set the organizer status.
     */
    public function setOrganizer(bool $status): bool
    {
        $this->organizer = $status;
        if (!$status) {
            $this->verified_organizer = null;
        }
        $this->save();
        
        return $this->organizer;
    }

    /**
     * Get the ticket purchases for this user.
     */
    public function ticketPurchases()
    {
        return $this->hasMany(TicketPurchase::class);
    }

    /**
     * Get the ticket information records for this user.
     */
    public function ticketInformation()
    {
        return $this->hasMany(EventTicketInformation::class);
    }

    /**
     * Event activity logs performed by this user.
     */
    public function eventActivityLogs(): HasMany
    {
        return $this->hasMany(EventActivityLog::class, 'user_id');
    }

    /**
     * Get the events created by this user.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    /**
     * Get the user's current active subscription.
     */
    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->active()
            ->with('plan')
            ->first();
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->currentSubscription() !== null;
    }

    /**
     * Check if user can create a new event.
     */
    public function canCreateEvent(): bool
    {
        $subscription = $this->currentSubscription();
        
        if (!$subscription) {
            // No subscription - check if free tier is available
            $freePlan = SubscriptionPlan::where('slug', 'free')->first();
            if (!$freePlan) {
                return false;
            }
            
            // Check free tier limits (only published events count)
            $currentMonthStart = now()->startOfMonth();
            $publishedEventsThisMonth = $this->events()
                ->where('status', 'published')
                ->where('created_at', '>=', $currentMonthStart)
                ->count();
            
            return $publishedEventsThisMonth < $freePlan->events_per_month;
        }

        return $subscription->canCreateEvent();
    }

    /**
     * Get remaining events for current month.
     */
    public function getRemainingEventsThisMonth(): ?int
    {
        $subscription = $this->currentSubscription();
        
        if (!$subscription) {
            $freePlan = SubscriptionPlan::where('slug', 'free')->first();
            if (!$freePlan || $freePlan->isUnlimited('events_per_month')) {
                return null;
            }
            
            $currentMonthStart = now()->startOfMonth();
            $publishedEventsThisMonth = $this->events()
                ->where('status', 'published')
                ->where('created_at', '>=', $currentMonthStart)
                ->count();
            
            return max(0, $freePlan->events_per_month - $publishedEventsThisMonth);
        }

        return $subscription->getEventsRemainingThisMonth();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }
}