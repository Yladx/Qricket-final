<?php

namespace App\Http\Controllers;

use App\Services\ProfanityFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfanityFilterController extends Controller
{
    /**
     * Filter profanity from text in real-time
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:10000',
        ]);

        $text = $request->input('text');
        
        // Remove profanity completely (empty replacement)
        // The filter service already handles word boundaries correctly
        $filteredText = ProfanityFilterService::filterProfanity($text, '');
        
        // Minimal space cleanup - ONLY remove spaces that are clearly orphaned
        // This happens when a word is removed and leaves "space space" pattern
        // We'll only clean up double spaces that are definitely from word removal
        if ($filteredText !== $text) {
            // Only remove double spaces that appear between words (not at start/end)
            // Pattern: non-space, space, space, non-space -> replace with single space
            $filteredText = preg_replace('/(\S)\s{2,}(\S)/', '$1 $2', $filteredText);
        }
        
        return response()->json([
            'success' => true,
            'original' => $text,
            'filtered' => $filteredText,
            'hasProfanity' => ProfanityFilterService::containsProfanity($text),
        ]);
    }
}



