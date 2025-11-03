<?php

namespace App\Http\Controllers\Organizer\EventManagement\Traits;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait DraftHelpers
{
    protected function autoSaveDraft(array $step1Data): void
    {
        try {
            $draftId = session('event_data.draft_id');
            if ($draftId) {
                $draft = Event::find($draftId);
                if ($draft && $draft->status === 'draft') {
                    $draft->update($step1Data);
                    return;
                }
            }
            $basicinfoId = session('event_data.basicinfo_id');
            $draft = Event::create(array_merge($step1Data, [
                'user_id' => Auth::id(),
                'status' => 'draft',
                'basicinfo_id' => $basicinfoId,
            ]));
            session(['event_data.draft_id' => $draft->id]);
        } catch (\Throwable $e) {
            \Log::error('autoSaveDraft failed', ['error' => $e->getMessage()]);
        }
    }

    protected function uploadToR2($file, ?string $filename = null, ?int $eventId = null): string
    {
        $userId = Auth::id();
        $eventDir = $eventId ? "events/{$eventId}" : "events/temp/{$userId}";
        if (!$filename) {
            $filename = now()->format('Ymd_His') . '_' . \Str::random(8) . '.' . $file->getClientOriginalExtension();
        }
        return Storage::disk('r2')->putFileAs($eventDir, $file, $filename);
    }
}


