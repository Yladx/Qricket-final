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
        'f***',
        'f**k',
        'sh**',
        'b****',
        'a**',
        'a**hole',
        'd**n',
        'h**l',
        'c**p',
        'p**s',
        'c**t',
        'b***h',
        'n***a',
        'n***er',
        'r**tard',
        'f*g',
        'f*ggot',
        // Variations and common misspellings
        'fuck',
        'shit',
        'bitch',
        'ass',
        'damn',
        'hell',
        'crap',
        'piss',
        'cunt',
        'retard',
        'fag',
        'faggot',
        'nigger',
        'nigga',
        'asshole',
        'bastard',
        'dick',
        'cock',
        'pussy',
        'whore',
        'slut',
        'tits',
        'boobs',
        'breasts',
        '🖕',
        // Additional offensive terms
        'kys',
        'kill yourself',
        'die',
        'suicide',
        // Common leetspeak variations
        'f*ck',
        'sh*t',
        'b*tch',
        'a$$',
        'd*ck',
        'bitch',
        'klarence bakla',
        'gago',
        'gaga',
        'gagu',
        'gagi',
        'tanga',
        'bobo',
        'inutil',
        'engot',
        'ulol',
        'baliw',
        'sirang-sira',
        'puta',
        'putangina',
        'putang ina',
        'amputa',
        'hayup',
        'lintik',
        'leche',
        'punyeta',
        'bwisit',
        'bwiset',
        'tarantado',
        'puke',
        'pekpek',
        'kiki',
        'titi',
        'burat',
        'tarugo',
        'etits',
        'jakol',
        'jumabols',
        'tite',
        'dede',
        'suso',
        'tae',
        'jebs',
        'dumi',
        'ipot',
        'pwet',
        'puwet',
        'wetpaks',
        'kiyeme',
        'amp',
        'pa',
        'pi',
        'pakyu',
        'pakshet',
        'shet',
        'shunga',
        'bano',
        'ogag',
        'olol',
        'bopols',
        'bangag',
        'hindot',
        'hinayupak',
        'pokpok',
        'bayag',
        'kiffy',
        'pisting',
        'pisting yawa',
        'yawa',
        'pukinang',
        'kantot',
        'salsal',
        'supot',
        'timang',
        'gunggong',
        'tangina',
        'udjo',
        'vovo',
        'bogok',
        'nyeta',
        'puchanggala',
        'pukingina',
        'ungas',
        'walang kwenta',
        'basura',
        'skwater',
        'jeproks',
        'jologs',
        'damuho',
        'malibog',
        'manyak',
        'halang ang bituka',
        'salot',
        'buang',
        'luka-luka',
        'siraulo',
        'kolokoy',
        'tng',
        'pucha',
        'utin',
        'bulbol',
        'betlog',
        'tamod',
        'pepe',
        'kanto',
        'kubol',
        'kalunya',
        'kerida',
        'burikat',
        'laspag',
        'biyak',
        'panganid',
        'sanlang',
        'tanso',
        'sira ulo',
        'libog',
        'hampaslupa',
        'sawi',
        'kiri',
        'bakla',
        'tomboy',
        'tibo',
        'binabae',
        'bayot',
        'agui',
        'beki',
        'shokoy',
        'tikbalang',
        'tiyanak',
        'manananggal',
        'multo',
        'demonyo',
        'santong kabayo',
        'santo santo',
        'bruha',
        'mangkukulam',
        'ampota',
        'ampucha',

        /*
        |--------------------------------------------------------------------------
        | English Profanity Words
        |--------------------------------------------------------------------------
        */

        // // Scatological/Obscene
        // '2g1c', '2 girls 1 cup', 'blue waffle', 'cleveland steamer', 'dirty sanchez', 'blumpkin', 'brown showers', 'poop chute', 'poopchute', 'shit',
        // 'shitblimp', 'shitty', 'scat', 'goodpoop', 'piss pig', 'pisspig', 'golden shower', 'yellow showers', 'snowballing', 'rusty trombone',

        // // Sexual Acts/Terms
        // 'anal', 'anilingus', 'anus', 'auto erotic', 'autoerotic', 'bareback', 'barely legal', 'bdsm', 'bondage', 'boner',
        'boob',
        'boobs',
        'busty',
        'butt',
        'buttcheeks',
        'butthole',
        'camel toe',
        'clit',
        'clitoris',
        'cock',
        'cocks',
        'cumming',
        'cumshot',
        'cumshots',
        'cunnilingus',
        'deep throat',
        'deepthroat',
        'dildo',
        'doggie style',
        'doggiestyle',
        'doggy style',
        'doggystyle',
        'dog style',
        'double penetration',
        'dp action',
        'dry hump',
        'ejaculation',
        'erotic',
        'erotism',
        'fellatio',
        'foot fetish',
        'footjob',
        'fuck',
        'fuck buttons',
        'fuckin',
        'fucking',
        'fudge packer',
        'fudgepacker',
        'gangbang',
        'gang bang',
        'gay sex',
        'giant cock',
        'girl on top',
        'group sex',
        'g-spot',
        // 'hand job', 'handjob', 'hard core', 'hardcore', 'hentai', 'horny', 'incest', 'intercourse', 'jack off', 'jerk off',
        // 'jizz', 'kinkster', 'kinky', 'leather restraint', 'leather straight jacket', 'livesex', 'lolita', 'lovemaking', 'make me come', 'male squirting',
        // 'masturbate', 'masturbating', 'masturbation', 'menage a trois', 'milf', 'missionary position', 'muff diver', 'muffdiving', 'nipple', 'nipples',
        // 'nsfw', 'nsfw images', 'nude', 'nudity', 'nympho', 'nymphomania', 'orgasm', 'orgy', 'panties', 'panty',
        // 'pegging', 'penis', 'phone sex', 'porn', 'porno', 'pornography', 'prince albert piercing', 'pubes', 'pussy', 'queaf',
        // 'queef', 'quim', 'reverse cowgirl', 'rimjob', 'rimming', 'rosy palm', 'rosy palm and her 5 sisters', 'sadism', 'scissoring', 'semen',
        // 'sex', 'sexcam', 'sexo', 'sexy', 'sexual', 'sexually', 'sexuality', 'shaved beaver', 'shaved pussy', 'shemale',
        // 'shibari', 'skeet', 'splooge', 'splooge moose', 'spooge', 'spread legs', 'spunk', 'strap on', 'strapon', 'strappado',
        // 'strip club', 'style doggy', 'suck', 'sucks', 'sultry women', 'swinger', 'tainted love', 'tea bagging', 'threesome', 'throating',
        // 'thumbzilla', 'tied up', 'tight white', 'tit', 'tits', 'titties', 'titty', 'tongue in a', 'topless', 'tribadism',
        // 'tub girl', 'tubgirl', 'tushy', 'twat', 'twink', 'twinkie', 'undressing', 'upskirt', 'urethra play', 'urophilia',
        'vagina',
        'venus mound',
        'viagra',
        'vibrator',
        'violet wand',
        'vorarephilia',
        'voyeur',
        'voyeurweb',
        'voyuer',
        'vulva',
        // 'wank', 'wet dream', 'wrinkled starfish', 'yaoi', 'yiffy', 'zoophilia', 'fack',

        // // Slurs/Insults
        // 'apeshit', 'arsehole', 'ass', 'asshole', 'assmunch', 'bastard', 'bastardo', 'beaner', 'beaners', 'bulldyke',
        'bullshit',
        'coon',
        'coons',
        'darkie',
        'fag',
        'faggot',
        'honkey',
        'jigaboo',
        'jiggaboo',
        'jiggerboo',
        'kike',
        'mong',
        'motherfucker',
        'negro',
        'neonazi',
        'nigga',
        'nigger',
        'nig nog',
        'paki',
        'pikey',
        // 'poof', 'raghead', 'spastic', 'spic', 'tosser', 'towelhead', 'tranny', 'wetback', 'white power', 'whore', 'wigger',
        // 'breasts',

        // // Fetish/Paraphilia
        // 'acrotomophilia', 'beastiality', 'bestiality', 'coprolagnia', 'coprophilia', 'dendrophilia', 'dolcett', 'ecchi', 'figging', 'frotting',
        // 'futanari', 'gokkun', 'goregasm', 'guro', 'kinbaku', 'nambla', 'nawashi', 'octopussy', 'omorashi', 'ponyplay',
        // 'pthc', 'sadism', 'shota', 'shrimping', 'voyeur', 'wrapping men', 'bitch',

        // // Porn/Adult Industry
        // 'babeland', 'bangbros', 'bangbus', 'bimbos', 'birdlock', 'blonde action', 'blonde on blonde action', 'booty call', 'brunette action', 'bukkake',
        // 'camgirl', 'camslut', 'camwhore', 'circlejerk', 'escort', 'playboy', 'pleasure chest', 'pole smoker', 'porn', 'porno',
        // 'pornography', 'suicide girls', 'worldsex', 'xx', 'xxx',

        // // Miscellaneous/Other
        // 'ball gag', 'ball gravy', 'ball kicking', 'ball licking', 'ball sack', 'ball sucking', 'barenaked', 'bastinado', 'bbw', 'beaner',
        // 'beaver cleaver', 'beaver lips', 'big black', 'big breasts', 'big knockers', 'big tits', 'black cock', 'blue waffle', 'bondage', 'boner',
        // 'bung hole', 'bunghole', 'carpet muncher', 'carpetmuncher', 'chocolate rosebuds', 'cialis', 'clover clamps', 'clusterfuck', 'cornhole', 'creampie',
        // 'date rape', 'daterape', 'dick', 'dingleberry', 'dingleberries', 'dirty pillows', 'dominatrix', 'dommes', 'donkey punch', 'double dong',
        // 'dvda', 'eat my ass', 'eunuch', 'female squirting', 'femdom', 'fingerbang', 'girls gone wild', 'goatcx', 'goatse', 'damn', 'god damn',
        // 'goo girl', 'grope', 'hooker', 'hot carl', 'hot chick', 'how to kill', 'how to murder', 'huge fat', 'humping', 'jack off',
        // 'jail bait', 'jailbait', 'jelly donut', 'juggs', 'knobbing', 'lemon party', 'mr hands', 'mound of venus', 'one cup two girls', 'one guy one jar',
        // 'paedophile', 'pedobear', 'pedophile', 'piece of shit', 'pleasure chest', 'poontang', 'punany', 'prince albert piercing', 'pubes', 'queaf',
        // 'raging boner', 'rape', 'raping', 'rapist', 'rectum', 'santorum', 'schlong', 'semen', 'shaved beaver', 'shaved pussy',
        // 'smut', 'snatch', 'sodomize', 'sodomy', 'spunk', 'strap on', 'strip club', 'suck', 'swastika', 'taste my',
        // 'tied up', 'topless', 'towelhead', 'tranny', 'tub girl', 'tubgirl', 'twink', 'twinkie', 'two girls one cup', 'wrapping men',
        // '🖕',

        // // Additional words
        'arse',
        'asses',
        'assface',
        'assfaces',
        'assholes',
        'bastards',
        'bellend',
        'bint',
        'bitches',
        'bitchy',
        'blowjob',
        'blump',
        'bollocks',
        'bollox',
        // 'buttpirate', 'buttplug', 'carpetmuncher', 'chinc', 'chink', 'choad', 'chode', 'cocksucker', 'cocksuckers', 'cocksucking', 'coochie', 'coochy',
        // 'cooter', 'doochbag', 'doosh', 'douche', 'douchebag', 'dumbass', 'dumbasses', 'dyke', 'fagget', 'faggit', 'faggots', 'fagtard', 'fanny', 'feck',
        // 'felch', 'feltch', 'fingerbang', 'fuc', 'fucked', 'fuckedup', 'fucker', 'fuckers', 'fuckoff', 'fucks', 'fuckup', 'fuk', 'fukker', 'fukkers', 'fuq',
        // 'gash', 'goddamn', 'goddamnit', 'gooch', 'gook', 'guido', 'heeb', 'jackass', 'jackasses', 'jackoff', 'jap', 'jerkoff', 'junglebunny', 'knobbing',
        // 'kooch', 'kootch', 'kraut', 'kyke', 'lesbo', 'lezzie', 'minge', 'motherfuckers', 'motherfucking', 'muff', 'munging', 'munter', 'ngga', 'niga',
        // 'niggers', 'niglet', 'nigr', 'panooch', 'pecker', 'peckerhead', 'pillock', 'piss', 'pissed', 'pollock', 'poon', 'poonani', 'poonany', 'porchmonkey',
        // 'prick', 'punani', 'punanny', 'pussie', 'pussies', 'puta', 'puto', 'ruski', 'scrote', 'shag', 'shite', 'shithead', 'shitheads', 'shits', 'shittier',
        // 'shittiest', 'shitting', 'skank', 'slag', 'slanteye', 'slut', 'smartass', 'smartasses', 'smeg', 'spick', 'teabagging', 'tities', 'wiseass',
        // 'wiseasses', 'wop', 'cunt',
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

        $textLower = strtolower($text);

        // Remove common punctuation and special characters for better matching
        $textClean = preg_replace('/[^a-z0-9\s]/', '', $textLower);

        // Check against profanity list - ONLY exact word matches
        foreach (self::$profanityList as $profanity) {
            $profanity = trim(strtolower($profanity));
            if (empty($profanity) || strlen($profanity) < 3) {
                continue;
            }

            // Only exact word match (with word boundaries) - no obfuscated detection
            if (preg_match('/\b' . preg_quote($profanity, '/') . '\b/i', $textClean)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a profanity word appears in text (fuzzy matching for obfuscation)
     * This detects profanity even when written with spaces or extra characters
     */
    private static function fuzzyMatchProfanity(string $text, string $profanity): bool
    {
        // Remove spaces and convert to lowercase for comparison
        $textNoSpaces = preg_replace('/\s+/', '', strtolower($text));
        $profanityClean = strtolower($profanity);

        // Check if profanity appears as substring (handles cases like "rfuck" containing "fuck")
        if (strpos($textNoSpaces, $profanityClean) !== false) {
            return true;
        }

        // Check if letters of profanity appear in sequence (handles cases like "r f u c k")
        $profanityLetters = str_split($profanityClean);
        $textIndex = 0;
        $matches = 0;

        foreach ($profanityLetters as $letter) {
            $found = strpos($textNoSpaces, $letter, $textIndex);
            if ($found !== false) {
                $matches++;
                $textIndex = $found + 1;
            }
        }

        // If 80% of letters match in sequence, consider it a match
        if ($matches >= (count($profanityLetters) * 0.8)) {
            return true;
        }

        return false;
    }

    /**
     * Find and replace obfuscated profanity in text
     * Handles cases where profanity is embedded in other text (e.g., "rfuck", "nboobs")
     */
    private static function replaceObfuscatedProfanity(string $text, string $profanity, string $replacement): string
    {
        $result = $text;
        $profanityLower = strtolower($profanity);
        $textNoSpaces = preg_replace('/\s+/', '', strtolower($text));

        // Find all occurrences of profanity in text without spaces
        $offset = 0;
        while (($pos = strpos($textNoSpaces, $profanityLower, $offset)) !== false) {
            // Map position back to original text
            $charCount = 0;
            $startPos = 0;
            $endPos = strlen($text);

            // Find start position in original text
            for ($i = 0; $i < strlen($text); $i++) {
                if (!ctype_space($text[$i])) {
                    if ($charCount == $pos) {
                        $startPos = $i;
                        break;
                    }
                    $charCount++;
                }
            }

            // Find end position
            $charCount = 0;
            for ($i = 0; $i < strlen($text); $i++) {
                if (!ctype_space($text[$i])) {
                    if ($charCount == $pos + strlen($profanity)) {
                        $endPos = $i;
                        break;
                    }
                    $charCount++;
                }
            }

            // Replace the matched portion
            $before = substr($result, 0, $startPos);
            $after = substr($result, $endPos);
            $result = $before . $replacement . $after;

            // Update textNoSpaces for next search
            $textNoSpaces = preg_replace('/\s+/', '', strtolower($result));
            $offset = $pos + strlen($replacement);
        }

        return $result;
    }

    public static function filterProfanity(string $text, string $replacement = '***'): string
    {
        if (empty($text)) {
            return $text;
        }

        $originalText = $text;

        // Only exact word matching with word boundaries - no obfuscated detection
        foreach (self::$profanityList as $profanity) {
            $profanity = trim($profanity);
            if (empty($profanity)) {
                continue;
            }

            // Skip single character words (too many false positives)
            if (strlen($profanity) < 3) {
                continue;
            }

            // Escape special regex characters
            $escapedProfanity = preg_quote($profanity, '/');

            // Check if it's a multi-word phrase
            if (strpos($profanity, ' ') !== false) {
                // For multi-word phrases, match the exact phrase with word boundaries
                $pattern = '/\b' . $escapedProfanity . '\b/iu';
                $originalText = preg_replace($pattern, $replacement, $originalText);
            } else {
                // For single words, use word boundaries for exact matches only
                $pattern = '/\b' . $escapedProfanity . '\b/iu';
                $originalText = preg_replace($pattern, $replacement, $originalText);
            }
        }

        // Clean up multiple spaces left after word removal (only between non-space characters)
        // This ensures "hello fuck world" -> "hello  world" becomes "hello world"
        $originalText = preg_replace('/(\S)\s{2,}(\S)/', '$1 $2', $originalText);
        // Also clean up spaces at the beginning/end if replacement was empty
        if ($replacement === '') {
            $originalText = trim($originalText);
            // Clean up any remaining multiple spaces
            $originalText = preg_replace('/\s+/', ' ', $originalText);
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

            if (
                preg_match('/\b' . preg_quote($profanity, '/') . '\b/i', $text) ||
                strpos($text, $profanity) !== false
            ) {
                $found[] = $profanity;
            }
        }

        return array_unique($found);
    }
}
