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
        $filteredText = ProfanityFilterService::filterProfanity($text, '');
        
        // Clean up spaces intelligently:
        // 1. Remove multiple consecutive spaces (2+) and replace with single space
        // 2. Remove spaces around removed words (space-word-space becomes nothing, space-word becomes nothing, word-space becomes nothing)
        // 3. But preserve the overall text structure - don't trim leading/trailing spaces if they were intentional
        
        // Remove multiple consecutive spaces (2 or more)
        $filteredText = preg_replace('/\s{2,}/', ' ', $filteredText);
        
        // Remove orphaned spaces (space-punctuation or punctuation-space patterns that don't make sense)
        // This handles cases like "word ! word" or "word, word" after word removal
        $filteredText = preg_replace('/\s+([.,!?;:])/', '$1', $filteredText); // Remove space before punctuation
        $filteredText = preg_replace('/([.,!?;:])\s+/', '$1 ', $filteredText); // Normalize space after punctuation
        
        return response()->json([
            'success' => true,
            'original' => $text,
            'filtered' => $filteredText,
            'hasProfanity' => ProfanityFilterService::containsProfanity($text),
        ]);
    }
}

