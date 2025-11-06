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
        
        // Only clean up spaces if text was actually filtered (to avoid unnecessary processing)
        if ($filteredText !== $text) {
            // Clean up spaces intelligently:
            // 1. Remove multiple consecutive spaces (3+) but allow double spaces
            // 2. Only remove spaces that are clearly orphaned
            
            // Remove multiple consecutive spaces (3 or more) - be more conservative
            $filteredText = preg_replace('/\s{3,}/', '  ', $filteredText);
            
            // Remove orphaned spaces before punctuation (but preserve structure)
            // Only remove if there are multiple spaces before punctuation
            $filteredText = preg_replace('/\s{2,}([.,!?;:])/', '$1', $filteredText);
            
            // Normalize spaces after punctuation (ensure single space)
            $filteredText = preg_replace('/([.,!?;:])\s{2,}/', '$1 ', $filteredText);
        }
        
        return response()->json([
            'success' => true,
            'original' => $text,
            'filtered' => $filteredText,
            'hasProfanity' => ProfanityFilterService::containsProfanity($text),
        ]);
    }
}

