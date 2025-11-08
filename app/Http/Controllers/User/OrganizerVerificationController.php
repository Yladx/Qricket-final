<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\OrganizerVerification;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizerVerificationController extends Controller
{
    /**
     * Show the verification form.
     */
    public function show()
    {
        $user = Auth::user();
        
        // Redirect if user is already a verified organizer
        if ($user->verified_organizer) {
            return redirect()->route('organizer.dashboard')
                ->with('info', 'You are already a verified organizer.');
        }
        
        // Check if user already has a verification request
        $verification = OrganizerVerification::where('user_id', $user->id)->latest()->first();
        
        return view('organizer.verification', compact('verification'));
    }

    /**
     * Store the verification request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Redirect if user is already a verified organizer
        if ($user->verified_organizer) {
            return redirect()->route('organizer.dashboard')
                ->with('info', 'You are already a verified organizer.');
        }
        
        // Check if user already has a pending verification
        $existingVerification = OrganizerVerification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
            
        if ($existingVerification) {
            return back()->with('warning', 'You already have a pending verification request.');
        }

        // Check if user has a rejected verification within the last 24 hours
        $recentRejection = OrganizerVerification::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->where('reviewed_at', '>=', now()->subDay())
            ->first();
            
        if ($recentRejection) {
            $hoursLeft = now()->diffInHours($recentRejection->reviewed_at->addDay(), false);
            return back()->with('error', "You can only submit a new verification request 24 hours after rejection. Please wait {$hoursLeft} more hours.");
        }

        $request->validate([
            // Personal Information
            'full_name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'birthdate' => 'required|date|before:today|before:-18 years',
            'nationality' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'father_name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'mother_name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'occupation' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'employer' => ['nullable', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'professional_experience' => ['nullable', 'string', 'max:1000', new \App\Rules\NoProfanity()],
            'alternate_email' => 'nullable|email|max:255',
            'emergency_contact_name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|in:spouse,parent,sibling,child,friend,other',
            
            // Document Verification
            'document_type' => 'required|string|in:id_card,passport,business_license,drivers_license,school_id,birth_certificate',
            'document_front' => 'required|file|mimes:jpg,jpeg,png|max:10240', // 10MB max - Images only
            'document_back' => 'nullable|file|mimes:jpg,jpeg,png|max:10240', // Optional for some document types
            
            // Organization Information
            'organization_name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'organization_description' => ['required', 'string', 'max:1000', new \App\Rules\NoProfanity()],
            'contact_phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'terms_accepted' => 'required|accepted',
        ], [
            'birthdate.before' => 'You must be at least 18 years old to become a verified organizer.',
            'document_front.mimes' => 'Only image files (JPG, PNG) are allowed.',
            'document_front.max' => 'File size must not exceed 10MB.',
            'document_back.mimes' => 'Only image files (JPG, PNG) are allowed.',
            'document_back.max' => 'File size must not exceed 10MB.',
            'terms_accepted.accepted' => 'You must accept the Terms and Conditions to proceed.',
        ]);

        // Additional validation based on document type
        $documentType = $request->document_type;
        if (in_array($documentType, ['id_card', 'business_license', 'school_id']) && !$request->hasFile('document_back')) {
            return back()->withErrors(['document_back' => 'This document type requires both front and back images.']);
        }

        // Upload documents to R2 storage
        $frontFile = $request->file('document_front');
        $frontFileName = 'organizer-verifications/' . $user->id . '/' . Str::uuid() . '.' . $frontFile->getClientOriginalExtension();
        
        $frontPath = Storage::disk('r2')->putFileAs(
            'organizer-verifications/' . $user->id,
            $frontFile,
            Str::uuid() . '.' . $frontFile->getClientOriginalExtension()
        );

        $backPath = null;
        if ($request->hasFile('document_back')) {
            $backFile = $request->file('document_back');
            $backPath = Storage::disk('r2')->putFileAs(
                'organizer-verifications/' . $user->id,
                $backFile,
                Str::uuid() . '.' . $backFile->getClientOriginalExtension()
            );
        }

        // Create verification record
        $verification = OrganizerVerification::create([
            'user_id' => $user->id,
            'status' => 'pending',
            
            // Personal Information
            'full_name' => $request->full_name,
            'birthdate' => $request->birthdate,
            'nationality' => $request->nationality,
            'address' => $request->address,
            'city' => $request->city,
            'state_province' => $request->state_province,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'occupation' => $request->occupation,
            'employer' => $request->employer,
            'professional_experience' => $request->professional_experience,
            'alternate_email' => $request->alternate_email,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            
            // Document Verification
            'document_type' => $request->document_type,
            'document_file_path' => $frontPath, // Keep for backward compatibility
            'document_front_path' => $frontPath,
            'document_back_path' => $backPath,
            
            // Organization Information
            'organization_name' => $request->organization_name,
            'organization_description' => $request->organization_description,
            'contact_phone' => $request->contact_phone,
            'website' => $request->website,
            
            // Terms and Conditions
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);

        // Log user activity
        $user->logActivity('organizer_verification_submitted', 'Organizer verification request submitted', [
            'verification_id' => $verification->id,
            'organization_name' => $verification->organization_name,
            'document_type' => $verification->document_type,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);
        // Create admin notification for organizer verification submission
        try {
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
            $notificationMessage = "{$userName} submitted an organizer verification request for '{$verification->organization_name}'";
            
            $notification = \App\Models\Notification::create([
                'user_id' => null, // Admin notification
                'admin_id' => null, // For all admins
                'type' => 'organizer',
                'action' => 'verified',
                'reference_id' => $verification->id,
                'title' => 'Organizer Verification Request',
                'message' => $notificationMessage,
                'data' => [
                    'verification_id' => $verification->id,
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'organization_name' => $verification->organization_name,
                    'document_type' => $verification->document_type,
                    'submitted_at' => $verification->created_at->toIso8601String(),
                ],
                'is_read' => false,
            ]);

            // Broadcast admin notification with notification ID
            try {
                event(new \App\Events\AdminNotification(
                    $notificationMessage,
                    'info',
                    'Organizer Verification Request',
                    $notification->id
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast admin notification for organizer verification: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification for organizer verification: ' . $e->getMessage());
        }

        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'verification_submitted');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after verification submission: ' . $e->getMessage());
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Verification request submitted. We will review it in 2–3 business days.');
    }

    /**
     * Admin: Show all verification requests.
     */
    public function index(Request $request)
    {
        $query = OrganizerVerification::with(['user', 'reviewer'])->latest();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $verifications = $query->paginate(20);
        
        return view('admin.verifications', compact('verifications'));
    }

    /**
     * Admin: Show specific verification details.
     */
    public function showAdmin(OrganizerVerification $verification)
    {
        $verification->load(['user', 'reviewer']);
        
        return view('admin.verification-details', compact('verification'));
    }

    /**
     * Admin: Approve verification.
     */
    public function approve(OrganizerVerification $verification)
    {
        $admin = Auth::guard('admin')->user();
        
        $verification->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Update user as verified organizer
        $verification->user->update([
            'organizer' => true,
            'verified_organizer' => now(),
        ]);

        // Log admin activity
        $admin->logActivity('verification_approved', [
            'verification_id' => $verification->id,
            'user_id' => $verification->user->id,
            'user_name' => $verification->user->first_name . ' ' . $verification->user->last_name,
            'organization_name' => $verification->organization_name,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip()
        ], 'Organizer verification approved');

        // Log user activity
        $verification->user->logActivity('organizer_verification_approved', 'Organizer verification request approved', [
            'verification_id' => $verification->id,
            'organization_name' => $verification->organization_name,
            'reviewed_by' => $admin->username,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip()
        ]);

        // Create notification for the user
        $notification = Notification::create([
            'user_id' => $verification->user_id,
            'admin_id' => null,
            'type' => 'organizer',
            'action' => 'accept',
            'reference_id' => $verification->id,
            'is_read' => false,
        ]);

        // Send real-time notification to user
        NotificationService::sendUserNotification(
            $verification->user_id,
            'Organizer Verification Approved',
            'Congratulations! Your organizer verification has been approved. You can now access organizer features and create events.',
            'success'
        );

        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'verification_approved');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after verification approval: ' . $e->getMessage());
        }

        return back()->with('success', 'Organizer verification approved.');
    }

    /**
     * Admin: Reject verification.
     */
    public function reject(Request $request, OrganizerVerification $verification)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $admin = Auth::guard('admin')->user();
        
        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Log admin activity
        $admin->logActivity('verification_rejected', [
            'verification_id' => $verification->id,
            'user_id' => $verification->user->id,
            'user_name' => $verification->user->first_name . ' ' . $verification->user->last_name,
            'organization_name' => $verification->organization_name,
            'rejection_reason' => $request->rejection_reason,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip()
        ], 'Organizer verification rejected');

        // Log user activity
        $verification->user->logActivity('organizer_verification_rejected', 'Organizer verification request rejected', [
            'verification_id' => $verification->id,
            'organization_name' => $verification->organization_name,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $admin->username,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip()
        ]);

        // Create notification for the user
        $notification = Notification::create([
            'user_id' => $verification->user_id,
            'admin_id' => null,
            'type' => 'organizer',
            'action' => 'reject',
            'reference_id' => $verification->id,
            'is_read' => false,
        ]);

        // Send real-time notification to user
        NotificationService::sendUserNotification(
            $verification->user_id,
            'Organizer Verification Rejected',
            'Your organizer verification request has been rejected. Reason: ' . $request->rejection_reason . ' You can submit a new verification request with updated information.',
            'error'
        );

        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'verification_rejected');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after verification rejection: ' . $e->getMessage());
        }

        return back()->with('warning', 'Organizer verification rejected and the user has been notified.');
    }
}
