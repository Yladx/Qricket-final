<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $originalData = $request->user()->only(['first_name', 'last_name', 'email', 'profile_picture']);
        
        $user = $request->user();
        $user->fill($request->validated());

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            
            \Log::info('Profile picture upload started', [
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType()
            ]);

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                \Log::warning('Invalid file type uploaded', [
                    'user_id' => $user->id,
                    'file_type' => $file->getMimeType()
                ]);
                return Redirect::route('profile.edit')->with('error', 'Invalid file type. Please upload a JPG or PNG image.');
            }

            // Validate file size (5MB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                \Log::warning('File too large uploaded', [
                    'user_id' => $user->id,
                    'file_size' => $file->getSize()
                ]);
                return Redirect::route('profile.edit')->with('error', 'File size must be less than 5MB.');
            }

            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('r2')->exists($user->profile_picture)) {
                try {
                    Storage::disk('r2')->delete($user->profile_picture);
                    \Log::info('Old profile picture deleted successfully', [
                        'user_id' => $user->id,
                        'old_file_path' => $user->profile_picture
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete old profile picture: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'old_file_path' => $user->profile_picture
                    ]);
                    // Continue with upload even if deletion fails
                }
            }

            // Upload new profile picture directly to profile folder
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            try {
                $filePath = Storage::disk('r2')->putFileAs(
                    'profile',
                    $file,
                    $fileName
                );

                // Verify the file was uploaded successfully
                if (!Storage::disk('r2')->exists($filePath)) {
                    throw new \Exception('File upload verification failed - file does not exist on R2');
                }

                \Log::info('Profile picture uploaded and verified successfully', [
                    'user_id' => $user->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_size' => Storage::disk('r2')->size($filePath),
                    'old_file_path' => $user->profile_picture
                ]);

                $user->profile_picture = $filePath;
            } catch (\Exception $e) {
                \Log::error('Profile picture upload failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'file_name' => $fileName,
                    'error_details' => $e->getTraceAsString()
                ]);
                
                return Redirect::route('profile.edit')->with('error', 'We couldn\'t upload your profile picture. Please try again.');
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Log profile update
        $changes = [];
        foreach (['first_name', 'last_name', 'email', 'profile_picture'] as $field) {
            if ($originalData[$field] !== $user->$field) {
                $changes[$field] = [
                    'from' => $originalData[$field],
                    'to' => $user->$field
                ];
            }
        }

        $user->logActivity('profile_update', 'Profile information updated', [
            'updated_fields' => array_keys($changes),
            'changes' => $changes,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        // Log organizer status change specifically if it changed
        if (isset($changes['organizer'])) {
            $status = $changes['organizer']['to'] ? 'enabled' : 'disabled';
            $user->logActivity('organizer_status_changed', "Organizer status {$status}", [
                'previous_status' => $changes['organizer']['from'],
                'new_status' => $changes['organizer']['to'],
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ]);
        }

        // Determine success message based on what was updated
        $successMessage = 'Your profile was updated.';
        if (isset($changes['profile_picture'])) {
            $successMessage = 'Your profile picture has been updated successfully!';
        }

        return Redirect::route('profile.edit')->with('success', $successMessage);
    }

    /**
     * Delete the user's account (soft delete - sets status to deleted).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log account deletion before soft deleting
        $user->logActivity('account_deleted', 'User account deleted (scheduled for permanent deletion in 30 days)', [
            'deletion_method' => 'user_request',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        // Send account deletion email notification
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\AccountDeletionMail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send account deletion email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // Soft delete the user (sets status to deleted and deleted_at timestamp)
        $user->softDelete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Your account has been scheduled for deletion. You have 30 days to restore it by contacting support.');
    }

    /**
     * Cancel account deletion (restore deleted account).
     */
    public function cancelDeletion(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find the deleted user by email
        $user = \App\Models\User::where('email', $request->email)
            ->where('status', \App\Models\User::STATUS_DELETED)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account scheduled for deletion was found with that email address.'
                ], 404);
            }
            return redirect()->route('account.status', ['type' => 'deleted'])
                ->with('error', 'No account scheduled for deletion was found with that email address.');
        }

        // Check if deletion can still be cancelled (within 30 days)
        $deletionDate = \Carbon\Carbon::parse($user->deleted_at);
        $daysRemaining = now()->diffInDays($deletionDate, false);
        
        if ($daysRemaining < -30) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The 30-day grace period has expired. Your account cannot be restored.'
                ], 400);
            }
            return redirect()->route('account.status', ['type' => 'deleted'])
                ->with('error', 'The 30-day grace period has expired. Your account cannot be restored.');
        }

        // Restore the user
        $user->restore();

        // Log account restoration
        $user->logActivity('account_deletion_cancelled', 'User cancelled account deletion and restored account', [
            'restoration_method' => 'user_request',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'days_remaining' => $daysRemaining
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your account deletion has been cancelled successfully. You can now log in to your account.'
            ]);
        }

        return redirect()->route('login')
            ->with('success', 'Your account deletion has been cancelled. You can now log in to your account.');
    }

    /**
     * Update the user's social media links.
     */
    public function updateSocialMedia(Request $request): RedirectResponse
    {
        $user = $request->user();
        $socialData = $request->input('social', []);

        $platformFormats = [
            'facebook' => 'https://facebook.com/{username}',
            'instagram' => 'https://instagram.com/{username}',
            'linkedin' => 'https://linkedin.com/in/{username}',
            'twitter' => 'https://twitter.com/{username}',
            'youtube' => 'https://youtube.com/@{username}',
            'tiktok' => 'https://tiktok.com/@{username}',
        ];

        foreach ($socialData as $platform => $data) {
            $value = trim($data['value'] ?? '');
            
            if (empty($value)) {
                // Remove link if empty
                \App\Models\SocialMediaLink::where('user_id', $user->id)
                    ->where('platform', $platform)
                    ->delete();
                continue;
            }

            // Determine if input is a URL or username
            $isUrl = filter_var($value, FILTER_VALIDATE_URL) !== false || 
                     str_starts_with($value, 'http://') || 
                     str_starts_with($value, 'https://');

            $username = null;
            $url = null;

            if ($isUrl) {
                // It's a full URL
                $url = $value;
                // Try to extract username from URL if possible
                $username = $this->extractUsernameFromUrl($value, $platform);
            } else {
                // It's a username - construct URL
                $username = $value;
                $urlFormat = $platformFormats[$platform] ?? '';
                if ($urlFormat) {
                    $url = str_replace('{username}', $value, $urlFormat);
                }
            }

            // Update or create social media link
            \App\Models\SocialMediaLink::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'platform' => $platform,
                ],
                [
                    'username' => $username,
                    'url' => $url,
                ]
            );
        }

        $user->logActivity('social_media_updated', 'Social media links updated', [
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        return Redirect::route('profile.edit')->with('status', 'social-media-updated');
    }

    /**
     * Extract username from a social media URL.
     */
    private function extractUsernameFromUrl(string $url, string $platform): ?string
    {
        $patterns = [
            'facebook' => ['/facebook\.com\/([^\/\?]+)/i', '/fb\.com\/([^\/\?]+)/i'],
            'instagram' => ['/instagram\.com\/([^\/\?]+)/i', '/instagr\.am\/([^\/\?]+)/i'],
            'linkedin' => ['/linkedin\.com\/in\/([^\/\?]+)/i', '/linkedin\.com\/company\/([^\/\?]+)/i'],
            'twitter' => ['/twitter\.com\/([^\/\?]+)/i', '/x\.com\/([^\/\?]+)/i'],
            'youtube' => ['/youtube\.com\/(?:@|channel\/|user\/|c\/)([^\/\?]+)/i', '/youtu\.be\/([^\/\?]+)/i'],
            'tiktok' => ['/tiktok\.com\/@([^\/\?]+)/i', '/vm\.tiktok\.com\/([^\/\?]+)/i'],
        ];

        if (!isset($patterns[$platform])) {
            return null;
        }

        foreach ($patterns[$platform] as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
