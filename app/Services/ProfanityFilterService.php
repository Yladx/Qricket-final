<?php

namespace App\Services;

class ProfanityFilterService
{
    /**
     * List of profanity words/phrases to filter
     * This is a basic list - you can expand it or load from a database/file
     */
    private static $profanityList = [
        // Common profanities (censored examples)
        'f***', 'f**k', 'sh**', 'b****', 'a**', 'a**hole',
        'd**n', 'h**l', 'c**p', 'p**s', 'c**t', 'b***h',
        'n***a', 'n***er', 'r**tard', 'f*g', 'f*ggot',
        // Variations and common misspellings
        'fuck', 'shit', 'bitch', 'ass', 'damn', 'hell',
        'crap', 'piss', 'cunt', 'retard', 'fag', 'faggot',
        'nigger', 'nigga', 'asshole', 'bastard', 'dick',
        'cock', 'pussy', 'whore', 'slut', 'tits', 'boobs', 'breasts', '🖕',
        // Additional offensive terms
        'kys', 'kill yourself', 'die', 'suicide',
        // Common leetspeak variations
        'f*ck', 'sh*t', 'b*tch', 'a$$', 'd*ck',
        // Partial matches (will be caught by contains check)
    ];

    /**
     * Check if text contains profanity
     *
     * @param string $text
     * @return bool
     */
    public static function containsProfanity(string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        $text = strtolower($text);
        
        // Remove common punctuation and special characters for better matching
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Check against profanity list
        foreach (self::$profanityList as $profanity) {
            $profanity = strtolower($profanity);
            
            // Exact word match (with word boundaries)
            if (preg_match('/\b' . preg_quote($profanity, '/') . '\b/i', $text)) {
                return true;
            }
            
            // Also check if profanity is contained (for partial matches)
            if (strpos($text, $profanity) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter profanity from text (replace with asterisks)
     *
     * @param string $text
     * @param string $replacement
     * @return string
     */
    public static function filterProfanity(string $text, string $replacement = '***'): string
    {
        if (empty($text)) {
            return $text;
        }

        $originalText = $text;
        $text = strtolower($text);
        
        foreach (self::$profanityList as $profanity) {
            $profanity = strtolower($profanity);
            
            // Replace with case-insensitive matching
            $pattern = '/\b' . preg_quote($profanity, '/') . '\b/i';
            $originalText = preg_replace($pattern, $replacement, $originalText);
            
            // Also handle partial matches
            $pattern = '/' . preg_quote($profanity, '/') . '/i';
            $originalText = preg_replace($pattern, $replacement, $originalText);
        }

        return $originalText;
    }

    /**
     * Get list of profanity words found in text
     *
     * @param string $text
     * @return array
     */
    public static function getProfanityFound(string $text): array
    {
        $found = [];
        
        if (empty($text)) {
            return $found;
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        foreach (self::$profanityList as $profanity) {
            $profanity = strtolower($profanity);
            
            if (preg_match('/\b' . preg_quote($profanity, '/') . '\b/i', $text) ||
                strpos($text, $profanity) !== false) {
                $found[] = $profanity;
            }
        }

        return array_unique($found);
    }
}

