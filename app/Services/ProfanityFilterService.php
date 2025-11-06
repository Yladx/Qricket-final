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
        
        'gago','gaga','gagu','gagi','tanga','bobo','inutil','engot','ulol','baliw','sirang-sira','puta','putangina','putang ina','amputa','animal','hayup','lintik','leche','punyeta','bwisit','bwiset','tarantado','puke','pekpek','kiki','titi','burat','tarugo','etits','jakol','jumabols','tite','dede','suso','tae','jebs','dumi','ipot','pwet','puwet','wetpaks','kiyeme','amp','pa','pi','pakyu','pakshet','shet','shunga','linta','bano','ogag','olol','bopols','bangag','hindot','hinayupak','pokpok','bayag','kiffy','pisting','pisting yawa','yawa','pukinang','kantot','salsal','supot','timang','gunggong','tangina','udjo','vovo','bogok','nyeta','puchanggala','pukingina','ungas','walang kwenta','basura','skwater','jeproks','jologs','damuho','malibog','manyak','halang ang bituka','salot','buang','luka-luka','siraulo','kolokoy','tng','pucha','utin','bulbol','betlog','tamod','pepe','kanto','kubol','kalunya','kerida','burikat','laspag','biyak','panganid','sanlang','tanso'
        ,'sira','sira ulo','libog','alipin','hampaslupa','sawi','kiri','bakla','tomboy','tibo','binabae','bayot','agui','beki','shokoy','tikbalang','tiyanak','manananggal','multo','demonyo','santong kabayo','santo santo','bruha','mangkukulam','ampota','ampucha',
        /*
        |--------------------------------------------------------------------------
        | English Profanity Words
        |--------------------------------------------------------------------------
        */
        
            // Scatological/Obscene
            '2g1c', '2 girls 1 cup', 'blue waffle', 'cleveland steamer', 'dirty sanchez', 'blumpkin', 'brown showers', 'poop chute', 'poopchute', 'shit',
            'shitblimp', 'shitty', 'scat', 'goodpoop', 'piss pig', 'pisspig', 'golden shower', 'yellow showers', 'snowballing', 'rusty trombone',
    
            // Sexual Acts/Terms
            'anal', 'anilingus', 'anus', 'auto erotic', 'autoerotic', 'bareback', 'barely legal', 'bdsm', 'bondage', 'boner',
            'boob', 'boobs', 'busty', 'butt', 'buttcheeks', 'butthole', 'camel toe', 'clit', 'clitoris', 'cock',
            'cocks', 'cum', 'cumming', 'cumshot', 'cumshots', 'cunnilingus', 'deep throat', 'deepthroat', 'dildo', 'doggie style',
            'doggiestyle', 'doggy style', 'doggystyle', 'dog style', 'double penetration', 'dp action', 'dry hump', 'ejaculation', 'erotic', 'erotism',
            'fellatio', 'fingering', 'fisting', 'foot fetish', 'footjob', 'fuck', 'fuck buttons', 'fuckin', 'fucking', 'fudge packer',
            'fudgepacker', 'gangbang', 'gang bang', 'gay sex', 'genitals', 'giant cock', 'girl on', 'girl on top', 'group sex', 'g-spot',
            'hand job', 'handjob', 'hard core', 'hardcore', 'hentai', 'horny', 'incest', 'intercourse', 'jack off', 'jerk off',
            'jizz', 'kinkster', 'kinky', 'leather restraint', 'leather straight jacket', 'livesex', 'lolita', 'lovemaking', 'make me come', 'male squirting',
            'masturbate', 'masturbating', 'masturbation', 'menage a trois', 'milf', 'missionary position', 'muff diver', 'muffdiving', 'nipple', 'nipples',
            'nsfw', 'nsfw images', 'nude', 'nudity', 'nympho', 'nymphomania', 'orgasm', 'orgy', 'panties', 'panty',
            'pegging', 'penis', 'phone sex', 'porn', 'porno', 'pornography', 'prince albert piercing', 'pubes', 'pussy', 'queaf',
            'queef', 'quim', 'reverse cowgirl', 'rimjob', 'rimming', 'rosy palm', 'rosy palm and her 5 sisters', 'sadism', 'scissoring', 'semen',
            'sex', 'sexcam', 'sexo', 'sexy', 'sexual', 'sexually', 'sexuality', 'shaved beaver', 'shaved pussy', 'shemale',
            'shibari', 'skeet', 'splooge', 'splooge moose', 'spooge', 'spread legs', 'spunk', 'strap on', 'strapon', 'strappado',
            'strip club', 'style doggy', 'suck', 'sucks', 'sultry women', 'swinger', 'tainted love', 'tea bagging', 'threesome', 'throating',
            'thumbzilla', 'tied up', 'tight white', 'tit', 'tits', 'titties', 'titty', 'tongue in a', 'topless', 'tribadism',
            'tub girl', 'tubgirl', 'tushy', 'twat', 'twink', 'twinkie', 'undressing', 'upskirt', 'urethra play', 'urophilia',
            'vagina', 'venus mound', 'viagra', 'vibrator', 'violet wand', 'vorarephilia', 'voyeur', 'voyeurweb', 'voyuer', 'vulva',
            'wank', 'wet dream', 'wrinkled starfish', 'yaoi', 'yiffy', 'zoophilia', 'fack',
    
            // Slurs/Insults
            'apeshit', 'arsehole', 'ass', 'asshole', 'assmunch', 'bastard', 'bastardo', 'beaner', 'beaners', 'bulldyke',
            'bullshit', 'coon', 'coons', 'darkie', 'fag', 'faggot', 'honkey', 'jigaboo', 'jiggaboo', 'jiggerboo',
            'kike', 'mong', 'motherfucker', 'negro', 'neonazi', 'nigga', 'nigger', 'nig nog', 'paki', 'pikey',
            'poof', 'raghead', 'spastic', 'spic', 'tosser', 'towelhead', 'tranny', 'wetback', 'white power', 'whore', 'wigger',
            'breasts',
    
            // Fetish/Paraphilia
            'acrotomophilia', 'beastiality', 'bestiality', 'coprolagnia', 'coprophilia', 'dendrophilia', 'dolcett', 'ecchi', 'figging', 'frotting',
            'futanari', 'gokkun', 'goregasm', 'guro', 'kinbaku', 'nambla', 'nawashi', 'octopussy', 'omorashi', 'ponyplay',
            'pthc', 'sadism', 'shota', 'shrimping', 'voyeur', 'wrapping men', 'bitch',
    
            // Porn/Adult Industry
            'babeland', 'bangbros', 'bangbus', 'bimbos', 'birdlock', 'blonde action', 'blonde on blonde action', 'booty call', 'brunette action', 'bukkake',
            'camgirl', 'camslut', 'camwhore', 'circlejerk', 'escort', 'playboy', 'pleasure chest', 'pole smoker', 'porn', 'porno',
            'pornography', 'suicide girls', 'worldsex', 'xx', 'xxx',
    
            // Miscellaneous/Other
            'ball gag', 'ball gravy', 'ball kicking', 'ball licking', 'ball sack', 'ball sucking', 'barenaked', 'bastinado', 'bbw', 'beaner',
            'beaver cleaver', 'beaver lips', 'big black', 'big breasts', 'big knockers', 'big tits', 'black cock', 'blue waffle', 'bondage', 'boner',
            'bung hole', 'bunghole', 'carpet muncher', 'carpetmuncher', 'chocolate rosebuds', 'cialis', 'clover clamps', 'clusterfuck', 'cornhole', 'creampie',
            'date rape', 'daterape', 'dick', 'dingleberry', 'dingleberries', 'dirty pillows', 'dominatrix', 'dommes', 'donkey punch', 'double dong',
            'dvda', 'eat my ass', 'eunuch', 'female squirting', 'femdom', 'fingerbang', 'girls gone wild', 'goatcx', 'goatse', 'damn', 'god damn',
            'goo girl', 'grope', 'hooker', 'hot carl', 'hot chick', 'how to kill', 'how to murder', 'huge fat', 'humping', 'jack off',
            'jail bait', 'jailbait', 'jelly donut', 'juggs', 'knobbing', 'lemon party', 'mr hands', 'mound of venus', 'one cup two girls', 'one guy one jar',
            'paedophile', 'pedobear', 'pedophile', 'piece of shit', 'pleasure chest', 'poontang', 'punany', 'prince albert piercing', 'pubes', 'queaf',
            'raging boner', 'rape', 'raping', 'rapist', 'rectum', 'santorum', 'schlong', 'semen', 'shaved beaver', 'shaved pussy',
            'smut', 'snatch', 'sodomize', 'sodomy', 'spunk', 'strap on', 'strip club', 'suck', 'swastika', 'taste my',
            'tied up', 'topless', 'towelhead', 'tranny', 'tub girl', 'tubgirl', 'twink', 'twinkie', 'two girls one cup', 'wrapping men',
            '🖕',
    
            // Additional words
            'arse', 'asses', 'assface', 'assfaces', 'assholes', 'bastards', 'bellend', 'bint', 'bitches', 'bitchy', 'blowjob', 'blump', 'bollocks', 'bollox',
            'buttpirate', 'buttplug', 'carpetmuncher', 'chinc', 'chink', 'choad', 'chode', 'cocksucker', 'cocksuckers', 'cocksucking', 'coochie', 'coochy',
            'cooter', 'doochbag', 'doosh', 'douche', 'douchebag', 'dumbass', 'dumbasses', 'dyke', 'fagget', 'faggit', 'faggots', 'fagtard', 'fanny', 'feck',
            'felch', 'feltch', 'fingerbang', 'fuc', 'fucked', 'fuckedup', 'fucker', 'fuckers', 'fuckoff', 'fucks', 'fuckup', 'fuk', 'fukker', 'fukkers', 'fuq',
            'gash', 'goddamn', 'goddamnit', 'gooch', 'gook', 'guido', 'heeb', 'jackass', 'jackasses', 'jackoff', 'jap', 'jerkoff', 'junglebunny', 'knobbing',
            'kooch', 'kootch', 'kraut', 'kyke', 'lesbo', 'lezzie', 'minge', 'motherfuckers', 'motherfucking', 'muff', 'munging', 'munter', 'ngga', 'niga',
            'niggers', 'niglet', 'nigr', 'panooch', 'pecker', 'peckerhead', 'pillock', 'piss', 'pissed', 'pollock', 'poon', 'poonani', 'poonany', 'porchmonkey',
            'prick', 'punani', 'punanny', 'pussie', 'pussies', 'puta', 'puto', 'ruski', 'scrote', 'shag', 'shite', 'shithead', 'shitheads', 'shits', 'shittier',
            'shittiest', 'shitting', 'skank', 'slag', 'slanteye', 'slut', 'smartass', 'smartasses', 'smeg', 'spick', 'teabagging', 'tities', 'wiseass',
            'wiseasses', 'wop', 'cunt',
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
        
        foreach (self::$profanityList as $profanity) {
            $profanity = trim($profanity);
            if (empty($profanity)) {
                continue;
            }
            
            // Escape special regex characters
            $escapedProfanity = preg_quote($profanity, '/');
            
            // Word boundary pattern (for whole words)
            $wordBoundaryPattern = '/\b' . $escapedProfanity . '\b/iu';
            $originalText = preg_replace($wordBoundaryPattern, $replacement, $originalText);
            
            // Also handle partial matches (without word boundaries) for multi-word phrases
            if (strpos($profanity, ' ') !== false) {
                $partialPattern = '/' . $escapedProfanity . '/iu';
                $originalText = preg_replace($partialPattern, $replacement, $originalText);
            } else {
                // For single words, also check without word boundaries (to catch words within other words)
                $partialPattern = '/' . $escapedProfanity . '/iu';
                $originalText = preg_replace($partialPattern, $replacement, $originalText);
            }
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

