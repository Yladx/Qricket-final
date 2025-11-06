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
        // Clean up extra spaces left after removing words
        $filteredText = preg_replace('/\s+/', ' ', $filteredText);
        $filteredText = trim($filteredText);
        
        return response()->json([
            'success' => true,
            'original' => $text,
            'filtered' => $filteredText,
            'hasProfanity' => ProfanityFilterService::containsProfanity($text),
        ]);
    }
}

