<?php
/**
 * Class to manage a UTF8 string
 * some functions from http://sourceforge.net/projects/phputf8
 *
 * @category   JawsType
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_UTF8
{
    /**
     * Detect if the string is UTF8 or not
     *
     * @param   string $string String to evaluate
     * @access  public
     * @return  bool    True if UTF8 encoding is detected, false if not
     */
    static function IsUTF8($str)
    {
        return preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $str);
    }

    /**
     * Encode text to UTF8
     * @param   string $str String to encode
     * @access  public
     * @return  string Encoded string
     */
    static function UTF8Encode($str)
    {
        return utf8_encode($str);
    }

    /**
     * Decode text to UTF8
     * @param   string $str String to decode
     * @access  public
     * @return  string Decoded string
     */
    static function UTF8Decode($str)
    {
        return utf8_decode($str);
    }

    /**
     * Get string length
     * @see http://www.php.net/strlen
     */
    static function strlen($str) {
        return strlen(utf8_decode($str));
    }

    /**
     * Split a string by string
     * @see http://www.php.net/explode
    */
    static function explode($delimiter, $str)
    {
        if ($delimiter == '') {
            return false;
        }
        return preg_split('!'.preg_quote($delimiter,'!').'!u', $str);
    }

    /**
     * Find position of first occurrence of a string
     * @see http://www.php.net/strpos
     */
    static function strpos($haystack, $needle, $offset = 0) {
        $comp = 0;
        while (!isset($length) || $length < $offset) {
            $pos = strpos($haystack, $needle, $offset + $comp);
            if ($pos === false) {
                return false;
            }
            $length = Jaws_UTF8::strlen(substr($haystack, 0, $pos));
            if ($length < $offset) $comp = $pos - $length;
        }

        return $length;
    }

    /**
     * Find position of last occurrence of a char in a string
     * @see http://www.php.net/strrpos
     */
    static function strrpos($haystack, $needle) {
        $pos = strrpos($haystack, $needle);

        if ($pos === false) {
            return false;
        } else {
            return Jaws_UTF8::strlen(substr($haystack, 0, $pos));
        }
    }

    /**
     * Return part of a string
     * @see http://www.php.net/substr
     */
    static function substr($str, $start , $length = null) {
        preg_match_all('/[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]/', $str, $arr);
        if (is_int($length)) {
            return implode('', array_slice($arr[0], $start, $length));
        } else {
            return implode('', array_slice($arr[0], $start));
        }
    }

    /**
     * Replace text within a portion of a string
     * @see http://www.php.net/substr_replace
    */
    static function substr_replace($str, $repl, $start, $length = null)
    {
        preg_match_all('/./us', $str, $ar);
        preg_match_all('/./us', $repl, $rar);
        if($length === null) {
            $length = Jaws_UTF8::strlen($str);
        }
        array_splice($ar[0], $start, $length, $rar[0]);
        return implode('',$ar[0]);
    }

    /**
     * Convert a string to an array
     * @see http://www.php.net/str_split
    */
    static function str_split($str, $split_len = 1)
    {
        if (!preg_match('/^[0-9]+$/',$split_len) || $split_len < 1) {
            return false;
        }
        $len = Jaws_UTF8::strlen($str);
        if ($len <= $split_len) {
            return array($str);
        }
        preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
        return $ar[0];
    }

    /**
     * Case insensivite string comparison
     * @see http://www.php.net/strcasecmp
    */
    static function strcasecmp($str1, $str2)
    {
        $str1 = Jaws_UTF8::strtolower($str1);
        $str2 = Jaws_UTF8::strtolower($str2);
        return strcmp($str1, $str2);
    }

    /**
     * Case-insensitive strstr
     * @see http://www.php.net/stristr
     */
    static function stristr($str, $search)
    {
        if (strlen($search) == 0) {
            return $str;
        }
        $lstr = Jaws_UTF8::strtolower($str);
        $lsearch = Jaws_UTF8::strtolower($search);
        preg_match('/^(.*)'.preg_quote($lsearch).'/Us',$lstr, $matches);
        if (count($matches) == 2) {
            return substr($str, strlen($matches[1]));
        }
        return false;
    }

    /**
     * Make a string's first character uppercase
     * @see http://www.php.net/ucfirst
     */
    static function ucfirst($str)
    {
        switch (Jaws_UTF8::strlen($str)) {
        case 0:
            return '';
            break;
        case 1:
            return Jaws_UTF8::strtoupper($str);
            break;
        default:
            preg_match('/^(.{1})(.*)$/us', $str, $matches);
            return Jaws_UTF8::strtoupper($matches[1]).$matches[2];
        }
    }

    /**
     * Uppercase the first character of each word in a string
     * @see http://www.php.net/ucwords
    */
    static function ucwords($str)
    {
        if (!function_exists('utf8_ucwords_callback')) {
            /**
            * Callback function for preg_replace_callback call in ucwords
            */
            function utf8_ucwords_callback($matches)
            {
                $leadingws = $matches[2];
                $ucfirst = Jaws_UTF8::strtoupper($matches[3]);
                $ucword  = Jaws_UTF8::substr_replace(ltrim($matches[0]), $ucfirst,0,1);
                return $leadingws . $ucword;
            }
        }
        // Note: [\x0c\x09\x0b\x0a\x0d\x20] matches;
        // form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns
        // This corresponds to the definition of a "word" defined at http://www.php.net/ucwords
        $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';
        return preg_replace_callback($pattern, 'utf8_ucwords_callback', $str);
    }

    /**
     * Make a string lowercase
     * @see http://www.php.net/strtolower
     */
    static function strtolower($str)
    {
        static $UTF8_UPPER_TO_LOWER = null;
        if (is_null($UTF8_UPPER_TO_LOWER) ) {
            $UTF8_UPPER_TO_LOWER = array(
            "Ｚ"=>"ｚ","Ｙ"=>"ｙ","Ｘ"=>"ｘ","Ｗ"=>"ｗ","Ｖ"=>"ｖ","Ｕ"=>"ｕ",
            "Ｔ"=>"ｔ","Ｓ"=>"ｓ","Ｒ"=>"ｒ","Ｑ"=>"ｑ","Ｐ"=>"ｐ","Ｏ"=>"ｏ",
            "Ｎ"=>"ｎ","Ｍ"=>"ｍ","Ｌ"=>"ｌ","Ｋ"=>"ｋ","Ｊ"=>"ｊ","Ｉ"=>"ｉ",
            "Ｈ"=>"ｈ","Ｇ"=>"ｇ","Ｆ"=>"ｆ","Ｅ"=>"ｅ","Ｄ"=>"ｄ","Ｃ"=>"ｃ",
            "Ｂ"=>"ｂ","Ａ"=>"ａ","Å"=>"å","K"=>"k","Ω"=>"ω","Ώ"=>"ώ",
            "Ὼ"=>"ὼ","Ό"=>"ό","Ὸ"=>"ὸ","Ῥ"=>"ῥ","Ύ"=>"ύ","Ὺ"=>"ὺ",
            "Ῡ"=>"ῡ","Ῠ"=>"� ","Ί"=>"ί","Ὶ"=>"ὶ","Ῑ"=>"ῑ","Ῐ"=>"ῐ",
            "Ή"=>"ή","Ὴ"=>"ὴ","Έ"=>"έ","Ὲ"=>"ὲ","Ά"=>"ά","Ὰ"=>"ὰ",
            "Ᾱ"=>"ᾱ","Ᾰ"=>"ᾰ","Ὧ"=>"ὧ","Ὦ"=>"ὦ","Ὥ"=>"ὥ","Ὤ"=>"ὤ",
            "Ὣ"=>"ὣ","Ὢ"=>"ὢ","Ὡ"=>"ὡ","Ὠ"=>"� ","Ὗ"=>"ὗ","Ὕ"=>"ὕ",
            "Ὓ"=>"ὓ","Ὑ"=>"ὑ","Ὅ"=>"ὅ","Ὄ"=>"ὄ","Ὃ"=>"ὃ","Ὂ"=>"ὂ",
            "Ὁ"=>"ὁ","Ὀ"=>"ὀ","Ἷ"=>"ἷ","Ἶ"=>"ἶ","Ἵ"=>"ἵ","Ἴ"=>"ἴ",
            "Ἳ"=>"ἳ","Ἲ"=>"ἲ","Ἱ"=>"ἱ","Ἰ"=>"ἰ","Ἧ"=>"ἧ","Ἦ"=>"ἦ",
            "Ἥ"=>"ἥ","Ἤ"=>"ἤ","Ἣ"=>"ἣ","Ἢ"=>"ἢ","Ἡ"=>"ἡ","Ἠ"=>"� ",
            "Ἕ"=>"ἕ","Ἔ"=>"ἔ","Ἓ"=>"ἓ","Ἒ"=>"ἒ","Ἑ"=>"ἑ","Ἐ"=>"ἐ",
            "Ἇ"=>"ἇ","Ἆ"=>"ἆ","Ἅ"=>"ἅ","Ἄ"=>"ἄ","Ἃ"=>"ἃ","Ἂ"=>"ἂ",
            "Ἁ"=>"ἁ","Ἀ"=>"ἀ","Ỹ"=>"ỹ","Ỷ"=>"ỷ","Ỵ"=>"ỵ","Ỳ"=>"ỳ",
            "Ự"=>"ự","Ữ"=>"ữ","Ử"=>"ử","Ừ"=>"ừ","Ứ"=>"ứ","Ủ"=>"ủ",
            "Ụ"=>"ụ","Ợ"=>"ợ","� "=>"ỡ","Ở"=>"ở","Ờ"=>"ờ","Ớ"=>"ớ",
            "Ộ"=>"ộ","Ỗ"=>"ỗ","Ổ"=>"ổ","Ồ"=>"ồ","Ố"=>"ố","Ỏ"=>"ỏ",
            "Ọ"=>"ọ","Ị"=>"ị","Ỉ"=>"ỉ","Ệ"=>"ệ","Ễ"=>"ễ","Ể"=>"ể",
            "Ề"=>"ề","Ế"=>"ế","Ẽ"=>"ẽ","Ẻ"=>"ẻ","Ẹ"=>"ẹ","Ặ"=>"ặ",
            "Ẵ"=>"ẵ","Ẳ"=>"ẳ","Ằ"=>"ằ","Ắ"=>"ắ","Ậ"=>"ậ","Ẫ"=>"ẫ",
            "Ẩ"=>"ẩ","Ầ"=>"ầ","Ấ"=>"ấ","Ả"=>"ả","� "=>"ạ","Ẕ"=>"ẕ",
            "Ẓ"=>"ẓ","Ẑ"=>"ẑ","Ẏ"=>"ẏ","Ẍ"=>"ẍ","Ẋ"=>"ẋ","Ẉ"=>"ẉ",
            "Ẇ"=>"ẇ","Ẅ"=>"ẅ","Ẃ"=>"ẃ","Ẁ"=>"ẁ","Ṿ"=>"ṿ","Ṽ"=>"ṽ",
            "Ṻ"=>"ṻ","Ṹ"=>"ṹ","Ṷ"=>"ṷ","Ṵ"=>"ṵ","Ṳ"=>"ṳ","Ṱ"=>"ṱ",
            "Ṯ"=>"ṯ","Ṭ"=>"ṭ","Ṫ"=>"ṫ","Ṩ"=>"ṩ","Ṧ"=>"ṧ","Ṥ"=>"ṥ",
            "Ṣ"=>"ṣ","� "=>"ṡ","Ṟ"=>"ṟ","Ṝ"=>"ṝ","Ṛ"=>"ṛ","Ṙ"=>"ṙ",
            "Ṗ"=>"ṗ","Ṕ"=>"ṕ","Ṓ"=>"ṓ","Ṑ"=>"ṑ","Ṏ"=>"ṏ","Ṍ"=>"ṍ",
            "Ṋ"=>"ṋ","Ṉ"=>"ṉ","Ṇ"=>"ṇ","Ṅ"=>"ṅ","Ṃ"=>"ṃ","Ṁ"=>"ṁ",
            "Ḿ"=>"ḿ","Ḽ"=>"ḽ","Ḻ"=>"ḻ","Ḹ"=>"ḹ","Ḷ"=>"ḷ","Ḵ"=>"ḵ",
            "Ḳ"=>"ḳ","Ḱ"=>"ḱ","Ḯ"=>"ḯ","Ḭ"=>"ḭ","Ḫ"=>"ḫ","Ḩ"=>"ḩ",
            "Ḧ"=>"ḧ","Ḥ"=>"ḥ","Ḣ"=>"ḣ","� "=>"ḡ","Ḟ"=>"ḟ","Ḝ"=>"ḝ",
            "Ḛ"=>"ḛ","Ḙ"=>"ḙ","Ḗ"=>"ḗ","Ḕ"=>"ḕ","Ḓ"=>"ḓ","Ḑ"=>"ḑ",
            "Ḏ"=>"ḏ","Ḍ"=>"ḍ","Ḋ"=>"ḋ","Ḉ"=>"ḉ","Ḇ"=>"ḇ","Ḅ"=>"ḅ",
            "Ḃ"=>"ḃ","Ḁ"=>"ḁ","Ֆ"=>"ֆ","Օ"=>"օ","Ք"=>"ք","Փ"=>"փ",
            "Ւ"=>"ւ","Ց"=>"ց","Ր"=>"ր","Տ"=>"տ","Վ"=>"վ","Ս"=>"ս",
            "Ռ"=>"ռ","Ջ"=>"ջ","Պ"=>"պ","Չ"=>"չ","Ո"=>"ո","Շ"=>"շ",
            "Ն"=>"ն","Յ"=>"յ","Մ"=>"մ","Ճ"=>"ճ","Ղ"=>"ղ","Ձ"=>"ձ",
            "Հ"=>"հ","Կ"=>"կ","Ծ"=>"ծ","Խ"=>"խ","Լ"=>"լ","Ի"=>"ի",
            "Ժ"=>"ժ","Թ"=>"թ","Ը"=>"ը","Է"=>"է","Զ"=>"զ","Ե"=>"ե",
            "Դ"=>"դ","Գ"=>"գ","Բ"=>"բ","Ա"=>"ա","Ԏ"=>"ԏ","Ԍ"=>"ԍ",
            "Ԋ"=>"ԋ","Ԉ"=>"ԉ","Ԇ"=>"ԇ","Ԅ"=>"ԅ","Ԃ"=>"ԃ","Ԁ"=>"ԁ",
            "Ӹ"=>"ӹ","Ӵ"=>"ӵ","Ӳ"=>"ӳ","Ӱ"=>"ӱ","Ӯ"=>"ӯ","Ӭ"=>"ӭ",
            "Ӫ"=>"ӫ","Ө"=>"ө","Ӧ"=>"ӧ","Ӥ"=>"ӥ","Ӣ"=>"ӣ","� "=>"ӡ",
            "Ӟ"=>"ӟ","Ӝ"=>"ӝ","Ӛ"=>"ӛ","Ә"=>"ә","Ӗ"=>"ӗ","Ӕ"=>"ӕ",
            "Ӓ"=>"ӓ","Ӑ"=>"ӑ","Ӎ"=>"ӎ","Ӌ"=>"ӌ","Ӊ"=>"ӊ","Ӈ"=>"ӈ",
            "Ӆ"=>"ӆ","Ӄ"=>"ӄ","Ӂ"=>"ӂ","Ҿ"=>"ҿ","Ҽ"=>"ҽ","Һ"=>"һ",
            "Ҹ"=>"ҹ","Ҷ"=>"ҷ","Ҵ"=>"ҵ","Ҳ"=>"ҳ","Ұ"=>"ұ","Ү"=>"ү",
            "Ҭ"=>"ҭ","Ҫ"=>"ҫ","Ҩ"=>"ҩ","Ҧ"=>"ҧ","Ҥ"=>"ҥ","Ң"=>"ң",
            "� "=>"ҡ","Ҟ"=>"ҟ","Ҝ"=>"ҝ","Қ"=>"қ","Ҙ"=>"ҙ","Җ"=>"җ",
            "Ҕ"=>"ҕ","Ғ"=>"ғ","Ґ"=>"ґ","Ҏ"=>"ҏ","Ҍ"=>"ҍ","Ҋ"=>"ҋ",
            "Ҁ"=>"ҁ","Ѿ"=>"ѿ","Ѽ"=>"ѽ","Ѻ"=>"ѻ","Ѹ"=>"ѹ","Ѷ"=>"ѷ",
            "Ѵ"=>"ѵ","Ѳ"=>"ѳ","Ѱ"=>"ѱ","Ѯ"=>"ѯ","Ѭ"=>"ѭ","Ѫ"=>"ѫ",
            "Ѩ"=>"ѩ","Ѧ"=>"ѧ","Ѥ"=>"ѥ","Ѣ"=>"ѣ","� "=>"ѡ","Я"=>"я",
            "Ю"=>"ю","Э"=>"э","Ь"=>"ь","Ы"=>"ы","Ъ"=>"ъ","Щ"=>"щ",
            "Ш"=>"ш","Ч"=>"ч","Ц"=>"ц","Х"=>"х","Ф"=>"ф","У"=>"у",
            "Т"=>"т","С"=>"с","� "=>"р","П"=>"п","О"=>"о","Н"=>"н",
            "М"=>"м","Л"=>"л","К"=>"к","Й"=>"й","И"=>"и","З"=>"з",
            "Ж"=>"ж","Е"=>"е","Д"=>"д","Г"=>"г","В"=>"в","Б"=>"б",
            "А"=>"а","Џ"=>"џ","Ў"=>"ў","Ѝ"=>"ѝ","Ќ"=>"ќ","Ћ"=>"ћ",
            "Њ"=>"њ","Љ"=>"љ","Ј"=>"ј","Ї"=>"ї","І"=>"і","Ѕ"=>"ѕ",
            "Є"=>"є","Ѓ"=>"ѓ","Ђ"=>"ђ","Ё"=>"ё","Ѐ"=>"ѐ","ϴ"=>"θ",
            "Ϯ"=>"ϯ","Ϭ"=>"ϭ","Ϫ"=>"ϫ","Ϩ"=>"ϩ","Ϧ"=>"ϧ","Ϥ"=>"ϥ",
            "Ϣ"=>"ϣ","� "=>"ϡ","Ϟ"=>"ϟ","Ϝ"=>"ϝ","Ϛ"=>"ϛ","Ϙ"=>"ϙ",
            "Ϋ"=>"ϋ","Ϊ"=>"ϊ","Ω"=>"ω","Ψ"=>"ψ","Χ"=>"χ","Φ"=>"φ",
            "Υ"=>"υ","Τ"=>"τ","Σ"=>"σ","Ρ"=>"ρ","� "=>"π","Ο"=>"ο",
            "Ξ"=>"ξ","Ν"=>"ν","Μ"=>"μ","Λ"=>"λ","Κ"=>"κ","Ι"=>"ι",
            "Θ"=>"θ","Η"=>"η","Ζ"=>"ζ","Ε"=>"ε","Δ"=>"δ","Γ"=>"γ",
            "Β"=>"β","Α"=>"α","Ώ"=>"ώ","Ύ"=>"ύ","Ό"=>"ό","Ί"=>"ί",
            "Ή"=>"ή","Έ"=>"έ","Ά"=>"ά","Ȳ"=>"ȳ","Ȱ"=>"ȱ","Ȯ"=>"ȯ",
            "Ȭ"=>"ȭ","Ȫ"=>"ȫ","Ȩ"=>"ȩ","Ȧ"=>"ȧ","Ȥ"=>"ȥ","Ȣ"=>"ȣ",
            "� "=>"ƞ","Ȟ"=>"ȟ","Ȝ"=>"ȝ","Ț"=>"ț","Ș"=>"ș","Ȗ"=>"ȗ",
            "Ȕ"=>"ȕ","Ȓ"=>"ȓ","Ȑ"=>"ȑ","Ȏ"=>"ȏ","Ȍ"=>"ȍ","Ȋ"=>"ȋ",
            "Ȉ"=>"ȉ","Ȇ"=>"ȇ","Ȅ"=>"ȅ","Ȃ"=>"ȃ","Ȁ"=>"ȁ","Ǿ"=>"ǿ",
            "Ǽ"=>"ǽ","Ǻ"=>"ǻ","Ǹ"=>"ǹ","Ƿ"=>"ƿ","Ƕ"=>"ƕ","Ǵ"=>"ǵ",
            "Ǳ"=>"ǳ","Ǯ"=>"ǯ","Ǭ"=>"ǭ","Ǫ"=>"ǫ","Ǩ"=>"ǩ","Ǧ"=>"ǧ",
            "Ǥ"=>"ǥ","Ǣ"=>"ǣ","� "=>"ǡ","Ǟ"=>"ǟ","Ǜ"=>"ǜ","Ǚ"=>"ǚ",
            "Ǘ"=>"ǘ","Ǖ"=>"ǖ","Ǔ"=>"ǔ","Ǒ"=>"ǒ","Ǐ"=>"ǐ","Ǎ"=>"ǎ",
            "Ǌ"=>"ǌ","Ǉ"=>"ǉ","Ǆ"=>"ǆ","Ƽ"=>"ƽ","Ƹ"=>"ƹ","Ʒ"=>"ʒ",
            "Ƶ"=>"ƶ","Ƴ"=>"ƴ","Ʋ"=>"ʋ","Ʊ"=>"ʊ","Ư"=>"ư","Ʈ"=>"ʈ",
            "Ƭ"=>"ƭ","Ʃ"=>"ʃ","Ƨ"=>"ƨ","Ʀ"=>"ʀ","Ƥ"=>"ƥ","Ƣ"=>"ƣ",
            "� "=>"ơ","Ɵ"=>"ɵ","Ɲ"=>"ɲ","Ɯ"=>"ɯ","Ƙ"=>"ƙ","Ɨ"=>"ɨ",
            "Ɩ"=>"ɩ","Ɣ"=>"ɣ","Ɠ"=>"� ","Ƒ"=>"ƒ","Ɛ"=>"ɛ","Ə"=>"ə",
            "Ǝ"=>"ǝ","Ƌ"=>"ƌ","Ɗ"=>"ɗ","Ɖ"=>"ɖ","Ƈ"=>"ƈ","Ɔ"=>"ɔ",
            "Ƅ"=>"ƅ","Ƃ"=>"ƃ","Ɓ"=>"ɓ","Ž"=>"ž","Ż"=>"ż","Ź"=>"ź",
            "Ÿ"=>"ÿ","Ŷ"=>"ŷ","Ŵ"=>"ŵ","Ų"=>"ų","Ű"=>"ű","Ů"=>"ů",
            "Ŭ"=>"ŭ","Ū"=>"ū","Ũ"=>"ũ","Ŧ"=>"ŧ","Ť"=>"ť","Ţ"=>"ţ",
            "� "=>"š","Ş"=>"ş","Ŝ"=>"ŝ","Ś"=>"ś","Ř"=>"ř","Ŗ"=>"ŗ",
            "Ŕ"=>"ŕ","Œ"=>"œ","Ő"=>"ő","Ŏ"=>"ŏ","Ō"=>"ō","Ŋ"=>"ŋ",
            "Ň"=>"ň","Ņ"=>"ņ","Ń"=>"ń","Ł"=>"ł","Ŀ"=>"ŀ","Ľ"=>"ľ",
            "Ļ"=>"ļ","Ĺ"=>"ĺ","Ķ"=>"ķ","Ĵ"=>"ĵ","Ĳ"=>"ĳ","İ"=>"i",
            "Į"=>"į","Ĭ"=>"ĭ","Ī"=>"ī","Ĩ"=>"ĩ","Ħ"=>"ħ","Ĥ"=>"ĥ",
            "Ģ"=>"ģ","� "=>"ġ","Ğ"=>"ğ","Ĝ"=>"ĝ","Ě"=>"ě","Ę"=>"ę",
            "Ė"=>"ė","Ĕ"=>"ĕ","Ē"=>"ē","Đ"=>"đ","Ď"=>"ď","Č"=>"č",
            "Ċ"=>"ċ","Ĉ"=>"ĉ","Ć"=>"ć","Ą"=>"ą","Ă"=>"ă","Ā"=>"ā",
            "Þ"=>"þ","Ý"=>"ý","Ü"=>"ü","Û"=>"û","Ú"=>"ú","Ù"=>"ù",
            "Ø"=>"ø","Ö"=>"ö","Õ"=>"õ","Ô"=>"ô","Ó"=>"ó","Ò"=>"ò",
            "Ñ"=>"ñ","Ð"=>"ð","Ï"=>"ï","Î"=>"î","Í"=>"í","Ì"=>"ì",
            "Ë"=>"ë","Ê"=>"ê","É"=>"é","È"=>"è","Ç"=>"ç","Æ"=>"æ",
            "Å"=>"å","Ä"=>"ä","Ã"=>"ã","Â"=>"â","Á"=>"á","À"=>"� ",
            "Z"=>"z","Y"=>"y","X"=>"x","W"=>"w","V"=>"v","U"=>"u",
            "T"=>"t","S"=>"s","R"=>"r","Q"=>"q","P"=>"p","O"=>"o",
            "N"=>"n","M"=>"m","L"=>"l","K"=>"k","J"=>"j","I"=>"i",
            "H"=>"h","G"=>"g","F"=>"f","E"=>"e","D"=>"d","C"=>"c",
            "B"=>"b","A"=>"a"
            );
        }

        return strtr($str, $UTF8_UPPER_TO_LOWER);
    }

    /**
     * Make a string uppercase
     * @see http://www.php.net/strtoupper
     */
    static function strtoupper($str)
    {
        static $UTF8_LOWER_TO_UPPER = null;
        if (is_null($UTF8_LOWER_TO_UPPER) ) {
            $UTF8_LOWER_TO_UPPER = array(
            "ｚ"=>"Ｚ","ｙ"=>"Ｙ","ｘ"=>"Ｘ","ｗ"=>"Ｗ","ｖ"=>"Ｖ","ｕ"=>"Ｕ",
            "ｔ"=>"Ｔ","ｓ"=>"Ｓ","ｒ"=>"Ｒ","ｑ"=>"Ｑ","ｐ"=>"Ｐ","ｏ"=>"Ｏ",
            "ｎ"=>"Ｎ","ｍ"=>"Ｍ","ｌ"=>"Ｌ","ｋ"=>"Ｋ","ｊ"=>"Ｊ","ｉ"=>"Ｉ",
            "ｈ"=>"Ｈ","ｇ"=>"Ｇ","ｆ"=>"Ｆ","ｅ"=>"Ｅ","ｄ"=>"Ｄ","ｃ"=>"Ｃ",
            "ｂ"=>"Ｂ","ａ"=>"Ａ","ῳ"=>"ῼ","ῥ"=>"Ῥ","ῡ"=>"Ῡ","� "=>"Ῠ",
            "ῑ"=>"Ῑ","ῐ"=>"Ῐ","ῃ"=>"ῌ","ι"=>"Ι","ᾳ"=>"ᾼ","ᾱ"=>"Ᾱ",
            "ᾰ"=>"Ᾰ","ᾧ"=>"ᾯ","ᾦ"=>"ᾮ","ᾥ"=>"ᾭ","ᾤ"=>"ᾬ","ᾣ"=>"ᾫ",
            "ᾢ"=>"ᾪ","ᾡ"=>"ᾩ","� "=>"ᾨ","ᾗ"=>"ᾟ","ᾖ"=>"ᾞ","ᾕ"=>"ᾝ",
            "ᾔ"=>"ᾜ","ᾓ"=>"ᾛ","ᾒ"=>"ᾚ","ᾑ"=>"ᾙ","ᾐ"=>"ᾘ","ᾇ"=>"ᾏ",
            "ᾆ"=>"ᾎ","ᾅ"=>"ᾍ","ᾄ"=>"ᾌ","ᾃ"=>"ᾋ","ᾂ"=>"ᾊ","ᾁ"=>"ᾉ",
            "ᾀ"=>"ᾈ","ώ"=>"Ώ","ὼ"=>"Ὼ","ύ"=>"Ύ","ὺ"=>"Ὺ","ό"=>"Ό",
            "ὸ"=>"Ὸ","ί"=>"Ί","ὶ"=>"Ὶ","ή"=>"Ή","ὴ"=>"Ὴ","έ"=>"Έ",
            "ὲ"=>"Ὲ","ά"=>"Ά","ὰ"=>"Ὰ","ὧ"=>"Ὧ","ὦ"=>"Ὦ","ὥ"=>"Ὥ",
            "ὤ"=>"Ὤ","ὣ"=>"Ὣ","ὢ"=>"Ὢ","ὡ"=>"Ὡ","� "=>"Ὠ","ὗ"=>"Ὗ",
            "ὕ"=>"Ὕ","ὓ"=>"Ὓ","ὑ"=>"Ὑ","ὅ"=>"Ὅ","ὄ"=>"Ὄ","ὃ"=>"Ὃ",
            "ὂ"=>"Ὂ","ὁ"=>"Ὁ","ὀ"=>"Ὀ","ἷ"=>"Ἷ","ἶ"=>"Ἶ","ἵ"=>"Ἵ",
            "ἴ"=>"Ἴ","ἳ"=>"Ἳ","ἲ"=>"Ἲ","ἱ"=>"Ἱ","ἰ"=>"Ἰ","ἧ"=>"Ἧ",
            "ἦ"=>"Ἦ","ἥ"=>"Ἥ","ἤ"=>"Ἤ","ἣ"=>"Ἣ","ἢ"=>"Ἢ","ἡ"=>"Ἡ",
            "� "=>"Ἠ","ἕ"=>"Ἕ","ἔ"=>"Ἔ","ἓ"=>"Ἓ","ἒ"=>"Ἒ","ἑ"=>"Ἑ",
            "ἐ"=>"Ἐ","ἇ"=>"Ἇ","ἆ"=>"Ἆ","ἅ"=>"Ἅ","ἄ"=>"Ἄ","ἃ"=>"Ἃ",
            "ἂ"=>"Ἂ","ἁ"=>"Ἁ","ἀ"=>"Ἀ","ỹ"=>"Ỹ","ỷ"=>"Ỷ","ỵ"=>"Ỵ",
            "ỳ"=>"Ỳ","ự"=>"Ự","ữ"=>"Ữ","ử"=>"Ử","ừ"=>"Ừ","ứ"=>"Ứ",
            "ủ"=>"Ủ","ụ"=>"Ụ","ợ"=>"Ợ","ỡ"=>"� ","ở"=>"Ở","ờ"=>"Ờ",
            "ớ"=>"Ớ","ộ"=>"Ộ","ỗ"=>"Ỗ","ổ"=>"Ổ","ồ"=>"Ồ","ố"=>"Ố",
            "ỏ"=>"Ỏ","ọ"=>"Ọ","ị"=>"Ị","ỉ"=>"Ỉ","ệ"=>"Ệ","ễ"=>"Ễ",
            "ể"=>"Ể","ề"=>"Ề","ế"=>"Ế","ẽ"=>"Ẽ","ẻ"=>"Ẻ","ẹ"=>"Ẹ",
            "ặ"=>"Ặ","ẵ"=>"Ẵ","ẳ"=>"Ẳ","ằ"=>"Ằ","ắ"=>"Ắ","ậ"=>"Ậ",
            "ẫ"=>"Ẫ","ẩ"=>"Ẩ","ầ"=>"Ầ","ấ"=>"Ấ","ả"=>"Ả","ạ"=>"� ",
            "ẛ"=>"� ","ẕ"=>"Ẕ","ẓ"=>"Ẓ","ẑ"=>"Ẑ","ẏ"=>"Ẏ","ẍ"=>"Ẍ",
            "ẋ"=>"Ẋ","ẉ"=>"Ẉ","ẇ"=>"Ẇ","ẅ"=>"Ẅ","ẃ"=>"Ẃ","ẁ"=>"Ẁ",
            "ṿ"=>"Ṿ","ṽ"=>"Ṽ","ṻ"=>"Ṻ","ṹ"=>"Ṹ","ṷ"=>"Ṷ","ṵ"=>"Ṵ",
            "ṳ"=>"Ṳ","ṱ"=>"Ṱ","ṯ"=>"Ṯ","ṭ"=>"Ṭ","ṫ"=>"Ṫ","ṩ"=>"Ṩ",
            "ṧ"=>"Ṧ","ṥ"=>"Ṥ","ṣ"=>"Ṣ","ṡ"=>"� ","ṟ"=>"Ṟ","ṝ"=>"Ṝ",
            "ṛ"=>"Ṛ","ṙ"=>"Ṙ","ṗ"=>"Ṗ","ṕ"=>"Ṕ","ṓ"=>"Ṓ","ṑ"=>"Ṑ",
            "ṏ"=>"Ṏ","ṍ"=>"Ṍ","ṋ"=>"Ṋ","ṉ"=>"Ṉ","ṇ"=>"Ṇ","ṅ"=>"Ṅ",
            "ṃ"=>"Ṃ","ṁ"=>"Ṁ","ḿ"=>"Ḿ","ḽ"=>"Ḽ","ḻ"=>"Ḻ","ḹ"=>"Ḹ",
            "ḷ"=>"Ḷ","ḵ"=>"Ḵ","ḳ"=>"Ḳ","ḱ"=>"Ḱ","ḯ"=>"Ḯ","ḭ"=>"Ḭ",
            "ḫ"=>"Ḫ","ḩ"=>"Ḩ","ḧ"=>"Ḧ","ḥ"=>"Ḥ","ḣ"=>"Ḣ","ḡ"=>"� ",
            "ḟ"=>"Ḟ","ḝ"=>"Ḝ","ḛ"=>"Ḛ","ḙ"=>"Ḙ","ḗ"=>"Ḗ","ḕ"=>"Ḕ",
            "ḓ"=>"Ḓ","ḑ"=>"Ḑ","ḏ"=>"Ḏ","ḍ"=>"Ḍ","ḋ"=>"Ḋ","ḉ"=>"Ḉ",
            "ḇ"=>"Ḇ","ḅ"=>"Ḅ","ḃ"=>"Ḃ","ḁ"=>"Ḁ","ֆ"=>"Ֆ","օ"=>"Օ",
            "ք"=>"Ք","փ"=>"Փ","ւ"=>"Ւ","ց"=>"Ց","ր"=>"Ր","տ"=>"Տ",
            "վ"=>"Վ","ս"=>"Ս","ռ"=>"Ռ","ջ"=>"Ջ","պ"=>"Պ","չ"=>"Չ",
            "ո"=>"Ո","շ"=>"Շ","ն"=>"Ն","յ"=>"Յ","մ"=>"Մ","ճ"=>"Ճ",
            "ղ"=>"Ղ","ձ"=>"Ձ","հ"=>"Հ","կ"=>"Կ","ծ"=>"Ծ","խ"=>"Խ",
            "լ"=>"Լ","ի"=>"Ի","ժ"=>"Ժ","թ"=>"Թ","ը"=>"Ը","է"=>"Է",
            "զ"=>"Զ","ե"=>"Ե","դ"=>"Դ","գ"=>"Գ","բ"=>"Բ","ա"=>"Ա",
            "ԏ"=>"Ԏ","ԍ"=>"Ԍ","ԋ"=>"Ԋ","ԉ"=>"Ԉ","ԇ"=>"Ԇ","ԅ"=>"Ԅ",
            "ԃ"=>"Ԃ","ԁ"=>"Ԁ","ӹ"=>"Ӹ","ӵ"=>"Ӵ","ӳ"=>"Ӳ","ӱ"=>"Ӱ",
            "ӯ"=>"Ӯ","ӭ"=>"Ӭ","ӫ"=>"Ӫ","ө"=>"Ө","ӧ"=>"Ӧ","ӥ"=>"Ӥ",
            "ӣ"=>"Ӣ","ӡ"=>"� ","ӟ"=>"Ӟ","ӝ"=>"Ӝ","ӛ"=>"Ӛ","ә"=>"Ә",
            "ӗ"=>"Ӗ","ӕ"=>"Ӕ","ӓ"=>"Ӓ","ӑ"=>"Ӑ","ӎ"=>"Ӎ","ӌ"=>"Ӌ",
            "ӊ"=>"Ӊ","ӈ"=>"Ӈ","ӆ"=>"Ӆ","ӄ"=>"Ӄ","ӂ"=>"Ӂ","ҿ"=>"Ҿ",
            "ҽ"=>"Ҽ","һ"=>"Һ","ҹ"=>"Ҹ","ҷ"=>"Ҷ","ҵ"=>"Ҵ","ҳ"=>"Ҳ",
            "ұ"=>"Ұ","ү"=>"Ү","ҭ"=>"Ҭ","ҫ"=>"Ҫ","ҩ"=>"Ҩ","ҧ"=>"Ҧ",
            "ҥ"=>"Ҥ","ң"=>"Ң","ҡ"=>"� ","ҟ"=>"Ҟ","ҝ"=>"Ҝ","қ"=>"Қ",
            "ҙ"=>"Ҙ","җ"=>"Җ","ҕ"=>"Ҕ","ғ"=>"Ғ","ґ"=>"Ґ","ҏ"=>"Ҏ",
            "ҍ"=>"Ҍ","ҋ"=>"Ҋ","ҁ"=>"Ҁ","ѿ"=>"Ѿ","ѽ"=>"Ѽ","ѻ"=>"Ѻ",
            "ѹ"=>"Ѹ","ѷ"=>"Ѷ","ѵ"=>"Ѵ","ѳ"=>"Ѳ","ѱ"=>"Ѱ","ѯ"=>"Ѯ",
            "ѭ"=>"Ѭ","ѫ"=>"Ѫ","ѩ"=>"Ѩ","ѧ"=>"Ѧ","ѥ"=>"Ѥ","ѣ"=>"Ѣ",
            "ѡ"=>"� ","џ"=>"Џ","ў"=>"Ў","ѝ"=>"Ѝ","ќ"=>"Ќ","ћ"=>"Ћ",
            "њ"=>"Њ","љ"=>"Љ","ј"=>"Ј","ї"=>"Ї","і"=>"І","ѕ"=>"Ѕ",
            "є"=>"Є","ѓ"=>"Ѓ","ђ"=>"Ђ","ё"=>"Ё","ѐ"=>"Ѐ","я"=>"Я",
            "ю"=>"Ю","э"=>"Э","ь"=>"Ь","ы"=>"Ы","ъ"=>"Ъ","щ"=>"Щ",
            "ш"=>"Ш","ч"=>"Ч","ц"=>"Ц","х"=>"Х","ф"=>"Ф","у"=>"У",
            "т"=>"Т","с"=>"С","р"=>"� ","п"=>"П","о"=>"О","н"=>"Н",
            "м"=>"М","л"=>"Л","к"=>"К","й"=>"Й","и"=>"И","з"=>"З",
            "ж"=>"Ж","е"=>"Е","д"=>"Д","г"=>"Г","в"=>"В","б"=>"Б",
            "а"=>"А","ϵ"=>"Ε","ϲ"=>"Σ","ϱ"=>"Ρ","ϰ"=>"Κ","ϯ"=>"Ϯ",
            "ϭ"=>"Ϭ","ϫ"=>"Ϫ","ϩ"=>"Ϩ","ϧ"=>"Ϧ","ϥ"=>"Ϥ","ϣ"=>"Ϣ",
            "ϡ"=>"� ","ϟ"=>"Ϟ","ϝ"=>"Ϝ","ϛ"=>"Ϛ","ϙ"=>"Ϙ","ϖ"=>"� ",
            "ϕ"=>"Φ","ϑ"=>"Θ","ϐ"=>"Β","ώ"=>"Ώ","ύ"=>"Ύ","ό"=>"Ό",
            "ϋ"=>"Ϋ","ϊ"=>"Ϊ","ω"=>"Ω","ψ"=>"Ψ","χ"=>"Χ","φ"=>"Φ",
            "υ"=>"Υ","τ"=>"Τ","σ"=>"Σ","ς"=>"Σ","ρ"=>"Ρ","π"=>"� ",
            "ο"=>"Ο","ξ"=>"Ξ","ν"=>"Ν","μ"=>"Μ","λ"=>"Λ","κ"=>"Κ",
            "ι"=>"Ι","θ"=>"Θ","η"=>"Η","ζ"=>"Ζ","ε"=>"Ε","δ"=>"Δ",
            "γ"=>"Γ","β"=>"Β","α"=>"Α","ί"=>"Ί","ή"=>"Ή","έ"=>"Έ",
            "ά"=>"Ά","ʒ"=>"Ʒ","ʋ"=>"Ʋ","ʊ"=>"Ʊ","ʈ"=>"Ʈ","ʃ"=>"Ʃ",
            "ʀ"=>"Ʀ","ɵ"=>"Ɵ","ɲ"=>"Ɲ","ɯ"=>"Ɯ","ɩ"=>"Ɩ","ɨ"=>"Ɨ",
            "ɣ"=>"Ɣ","� "=>"Ɠ","ɛ"=>"Ɛ","ə"=>"Ə","ɗ"=>"Ɗ","ɖ"=>"Ɖ",
            "ɔ"=>"Ɔ","ɓ"=>"Ɓ","ȳ"=>"Ȳ","ȱ"=>"Ȱ","ȯ"=>"Ȯ","ȭ"=>"Ȭ",
            "ȫ"=>"Ȫ","ȩ"=>"Ȩ","ȧ"=>"Ȧ","ȥ"=>"Ȥ","ȣ"=>"Ȣ","ȟ"=>"Ȟ",
            "ȝ"=>"Ȝ","ț"=>"Ț","ș"=>"Ș","ȗ"=>"Ȗ","ȕ"=>"Ȕ","ȓ"=>"Ȓ",
            "ȑ"=>"Ȑ","ȏ"=>"Ȏ","ȍ"=>"Ȍ","ȋ"=>"Ȋ","ȉ"=>"Ȉ","ȇ"=>"Ȇ",
            "ȅ"=>"Ȅ","ȃ"=>"Ȃ","ȁ"=>"Ȁ","ǿ"=>"Ǿ","ǽ"=>"Ǽ","ǻ"=>"Ǻ",
            "ǹ"=>"Ǹ","ǵ"=>"Ǵ","ǳ"=>"ǲ","ǯ"=>"Ǯ","ǭ"=>"Ǭ","ǫ"=>"Ǫ",
            "ǩ"=>"Ǩ","ǧ"=>"Ǧ","ǥ"=>"Ǥ","ǣ"=>"Ǣ","ǡ"=>"� ","ǟ"=>"Ǟ",
            "ǝ"=>"Ǝ","ǜ"=>"Ǜ","ǚ"=>"Ǚ","ǘ"=>"Ǘ","ǖ"=>"Ǖ","ǔ"=>"Ǔ",
            "ǒ"=>"Ǒ","ǐ"=>"Ǐ","ǎ"=>"Ǎ","ǌ"=>"ǋ","ǉ"=>"ǈ","ǆ"=>"ǅ",
            "ƿ"=>"Ƿ","ƽ"=>"Ƽ","ƹ"=>"Ƹ","ƶ"=>"Ƶ","ƴ"=>"Ƴ","ư"=>"Ư",
            "ƭ"=>"Ƭ","ƨ"=>"Ƨ","ƥ"=>"Ƥ","ƣ"=>"Ƣ","ơ"=>"� ","ƞ"=>"� ",
            "ƙ"=>"Ƙ","ƕ"=>"Ƕ","ƒ"=>"Ƒ","ƌ"=>"Ƌ","ƈ"=>"Ƈ","ƅ"=>"Ƅ",
            "ƃ"=>"Ƃ","ſ"=>"S","ž"=>"Ž","ż"=>"Ż","ź"=>"Ź","ŷ"=>"Ŷ",
            "ŵ"=>"Ŵ","ų"=>"Ų","ű"=>"Ű","ů"=>"Ů","ŭ"=>"Ŭ","ū"=>"Ū",
            "ũ"=>"Ũ","ŧ"=>"Ŧ","ť"=>"Ť","ţ"=>"Ţ","š"=>"� ","ş"=>"Ş",
            "ŝ"=>"Ŝ","ś"=>"Ś","ř"=>"Ř","ŗ"=>"Ŗ","ŕ"=>"Ŕ","œ"=>"Œ",
            "ő"=>"Ő","ŏ"=>"Ŏ","ō"=>"Ō","ŋ"=>"Ŋ","ň"=>"Ň","ņ"=>"Ņ",
            "ń"=>"Ń","ł"=>"Ł","ŀ"=>"Ŀ","ľ"=>"Ľ","ļ"=>"Ļ","ĺ"=>"Ĺ",
            "ķ"=>"Ķ","ĵ"=>"Ĵ","ĳ"=>"Ĳ","ı"=>"I","į"=>"Į","ĭ"=>"Ĭ",
            "ī"=>"Ī","ĩ"=>"Ĩ","ħ"=>"Ħ","ĥ"=>"Ĥ","ģ"=>"Ģ","ġ"=>"� ",
            "ğ"=>"Ğ","ĝ"=>"Ĝ","ě"=>"Ě","ę"=>"Ę","ė"=>"Ė","ĕ"=>"Ĕ",
            "ē"=>"Ē","đ"=>"Đ","ď"=>"Ď","č"=>"Č","ċ"=>"Ċ","ĉ"=>"Ĉ",
            "ć"=>"Ć","ą"=>"Ą","ă"=>"Ă","ā"=>"Ā","ÿ"=>"Ÿ","þ"=>"Þ",
            "ý"=>"Ý","ü"=>"Ü","û"=>"Û","ú"=>"Ú","ù"=>"Ù","ø"=>"Ø",
            "ö"=>"Ö","õ"=>"Õ","ô"=>"Ô","ó"=>"Ó","ò"=>"Ò","ñ"=>"Ñ",
            "ð"=>"Ð","ï"=>"Ï","î"=>"Î","í"=>"Í","ì"=>"Ì","ë"=>"Ë",
            "ê"=>"Ê","é"=>"É","è"=>"È","ç"=>"Ç","æ"=>"Æ","å"=>"Å",
            "ä"=>"Ä","ã"=>"Ã","â"=>"Â","á"=>"Á","� "=>"À","µ"=>"Μ",
            "z"=>"Z","y"=>"Y","x"=>"X","w"=>"W","v"=>"V","u"=>"U",
            "t"=>"T","s"=>"S","r"=>"R","q"=>"Q","p"=>"P","o"=>"O",
            "n"=>"N","m"=>"M","l"=>"L","k"=>"K","j"=>"J","i"=>"I",
            "h"=>"H","g"=>"G","f"=>"F","e"=>"E","d"=>"D","c"=>"C",
            "b"=>"B","a"=>"A"
            );
        }

        return strtr($str, $UTF8_LOWER_TO_UPPER);
    }

    /**
     * Replace all occurrences of the search string with the replacement string
     * @see http://www.php.net/str_replace
    */
    static function str_replace($search, $replace, $str)
    {
        if(!is_array($search)) {
            $search = '!'.preg_quote($search,'!').'!u';
        } else {
            foreach ($search as $k => $v) {
                $search[$k] = '!'.preg_quote($v).'!u';
            }
        }
        return preg_replace($search, $replace, $str);
    }

    /**
     * Case-insensitive version of str_replace
     * @see http://www.php.net/str_ireplace
     */
    static function str_ireplace($search, $replace, $str, $count = null)
    {
        if (!is_array($search)) {
            $slen = strlen($search);
            if ($slen == 0 ) {
                return $str;
            }
            $search = Jaws_UTF8::strtolower($search);
            $search = preg_quote($search);
            $lstr   = Jaws_UTF8::strtolower($str);
            $i = 0;
            $matched = 0;
            while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
                if ( $i === $count ) {
                    break;
                }
                $mlen = strlen($matches[0]);
                $lstr = substr($lstr, $mlen);
                $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
                        $matched += $mlen;
                $i++;
            }
            return $str;
        } else {
            foreach (array_keys($search) as $k) {
                if (is_array($replace)) {
                    if (array_key_exists($k,$replace)) {
                        $str = Jaws_UTF8::str_ireplace($search[$k], $replace[$k], $str, $count);
                    } else {
                        $str = Jaws_UTF8::str_ireplace($search[$k], '', $str, $count);
                    }
                } else {
                    $str = Jaws_UTF8::str_ireplace($search[$k], $replace, $str, $count);
                }
            }
            return $str;
        }
    }

    /**
     * Strip whitespace (or other characters) from the beginning of a string
     * @see http://www.php.net/ltrim
     */
    static function ltrim($str, $charlist='')
    {
        if($charlist == '') {
            return ltrim($str);
        }
        $charlist = preg_replace('!([\\\\\\-\\]\\[/])!','\\\${1}',$charlist);
        return preg_replace('/^['.$charlist.']+/u','',$str);
    }
 
    /**
     * Strip whitespace (or other characters) from the end of a string
     * @see http://www.php.net/rtrim
     */
    static function rtrim($str, $charlist='')
    {
        if($charlist == '') {
            return rtrim($str);
        }
        $charlist = preg_replace('!([\\\\\\-\\]\\[/])!','\\\${1}',$charlist);
        return preg_replace('/['.$charlist.']+$/u','',$str);
    }
 
    /**
     * Strip whitespace (or other characters) from the beginning and end of a string
     * @see http://www.php.net/trim
     */
    static function trim($str, $charlist='')
    {
        if($charlist == '') {
            return trim($str);
        }
        return Jaws_UTF8::ltrim(Jaws_UTF8::rtrim($str));
    }

    /**
     * Find length of initial segment not matching mask
     * @see http://www.php.net/strcspn
     */
    static function strcspn($str, $mask, $start = null, $length = null)
    {
        if ( empty($mask) || strlen($mask) == 0 ) {
            return null;
        }
        $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);
        if ($start !== null || $length !== null) {
            $str = Jaws_UTF8::substr($str, $start, $length);
        }
        preg_match('/^[^'.$mask.']+/u',$str, $matches);
        if ( isset($matches[0]) ) {
            return Jaws_UTF8::strlen($matches[0]);
        }
        return 0;
    }

    /**
     * Find length of initial segment matching mask
     * @see http://www.php.net/strspn
    */
    static function strspn($str, $mask, $start = null, $length = null)
    {
        $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);
        if ($start !== null || $length !== null) {
            $str = Jaws_UTF8::substr($str, $start, $length);
        }
        preg_match('/^['.$mask.']+/u',$str, $matches);
        if (isset($matches[0])) {
            return Jaws_UTF8::strlen($matches[0]);
        }
        return 0;
    }

    /**
     * Reverse a string
     * @see http://www.php.net/strrev
    */
    static function strrev($str)
    {
        preg_match_all('/./us', $str, $ar);
        return join('',array_reverse($ar[0]));
    }

    /**
     * Encode string by MIME header UTF-8 encoding
    */
    static function encode_mimeheader($str)
    {
        $length = 45; $pos = 0; $max = strlen($str);
        $buffer = '';
        while ($pos < $max) {
            if ($pos + $length < $max) {
                $adjust = 0;
                while (intval(ord($str[$pos + $length + $adjust]) & 0xC0) == 0x80) $adjust--;
                $buffer .= (empty($buffer)? '' : "?=\r\n =?UTF-8?B?") . base64_encode(substr($str, $pos, $length + $adjust));
                $pos = $pos + $length + $adjust;
            } else {
                $buffer .= (empty($buffer)? '' : "?=\r\n =?UTF-8?B?") . base64_encode(substr($str, $pos));
                $pos = $max;
            }
        }

        return '=?UTF-8?B?' . $buffer . '?=';
    }

    /**
     * Return a specific character
     * @see http://www.php.net/chr
    */
    static function chr($codes)
    {
        if (is_scalar($codes)) $codes= func_get_args();
        $str= '';
        foreach ($codes as $code) {
            if ($code < 128) {
                $str.= chr($code);
            }
            if ($code < 2048) {
                $str.= chr(($code >> 6) + 192) . chr(($code & 63) + 128);
            }
            if ($code < 65536) {
                $str.= chr(($code >> 12) + 224) . chr((($code >> 6) & 63) + 128) .
                       chr(($code & 63) + 128);
            }
            if ($code < 2097152) {
                $str.= chr(($code >> 18) + 240) . chr((($code >> 12) & 63) + 128) .
                       chr((($code >> 6) & 63) + 128) . chr(($code & 63) + 128);
            }
        }
        return $str;
    }

    /**
     * Return unicode ordinal of character
     * @see http://www.php.net/ord
     */
    static function ord($chr)
    {
        $ord0 = ord($chr);
        if ( $ord0 >= 0 && $ord0 <= 127 ) {
            return $ord0;
        }

        if ( !isset($chr{1}) ) {
            return false;
        }

        $ord1 = ord($chr{1});
        if ( $ord0 >= 192 && $ord0 <= 223 ) {
            return ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
        }

        if ( !isset($chr{2}) ) {
            return false;
        }
        $ord2 = ord($chr{2});
        if ( $ord0 >= 224 && $ord0 <= 239 ) {
            return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
        }

        if ( !isset($chr{3}) ) {
            return false;
        }
        $ord3 = ord($chr{3});
        if ($ord0>=240 && $ord0<=247) {
            return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
        }
    
        if ( !isset($chr{4}) ) {
            return false;
        }
        $ord4 = ord($chr{4});
        if ($ord0>=248 && $ord0<=251) {
            return ($ord0-248)*16777216 + ($ord1-128)*262144 + ($ord2-128)*4096 + ($ord3-128)*64 + ($ord4-128);
        }

        if ( !isset($chr{5}) ) {
            return false;
        }
        if ($ord0>=252 && $ord0<=253) {
            return ($ord0-252) * 1073741824 
                + ($ord1-128)*16777216 
                    + ($ord2-128)*262144 
                        + ($ord3-128)*4096 
                            + ($ord4-128)*64 
                                + (ord($c{5})-128);
        }
    
        if ( $ord0 >= 254 && $ord0 <= 255 ) { 
            return false;
        }
    }

    /**
     * Convert all HTML entities to their applicable characters
     * @see http://www.php.net/strrev
     */
    static function html_entity_decode($string, $quote_style = ENT_QUOTES)
    {
        return html_entity_decode($string, $quote_style, 'UTF-8');
    }

    /**
     * Returns the JSON representation of a value
     * @see http://www.php.net/json_encode
     */
    static function json_encode($value = false)
    {
        if (is_null($value)) {
            return 'null';
        }

        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        if (is_scalar($value)) {
            if (is_float($value)) {
                // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($value)));
            }

            if (is_string($value)) {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                                             array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
                                            );
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $value) . '"';
            } else {
                return $value;
            }
        }

        $isList = true;
        for ($i = 0, reset($value); $i < count($value); $i++, next($value)) {
            if (key($value) !== $i) {
                $isList = false;
                break;
            }
        }

        $result = array();
        if ($isList) {
            foreach ($value as $v) {
                $result[] = Jaws_UTF8::json_encode($v);
            }

            return '[' . implode(',', $result) . ']';
        } else {
            foreach ($value as $k => $v) $result[] = Jaws_UTF8::json_encode($k) . ':'. Jaws_UTF8::json_encode($v);
            return '{' . implode(',', $result) . '}';
        }
    }

    /**
     * Decodes a JSON string
     * @see http://www.php.net/json_decode
     * @extracted from PEAR::Services_JSON
     */
    static function json_decode($str)
    {
        $str = preg_replace(array('#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us'), '', $str);
        $str = trim($str);

        switch (strtolower($str)) {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            default:
                $m = array();

                if (is_numeric($str)) {
                    return ((float)$str == (integer)$str) ? (integer)$str : (float)$str;
                } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                    $delim = substr($str, 0, 1);
                    $chrs = substr($str, 1, -1);
                    $utf8 = '';
                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c < $strlen_chrs; ++$c) {

                        $substr_chrs_c_2 = substr($chrs, $c, 2);
                        $ord_chrs_c = ord($chrs{$c});

                        switch (true) {
                            case $substr_chrs_c_2 == '\b':
                                $utf8 .= chr(0x08);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\t':
                                $utf8 .= chr(0x09);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\n':
                                $utf8 .= chr(0x0A);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\f':
                                $utf8 .= chr(0x0C);
                                ++$c;
                                break;
                            case $substr_chrs_c_2 == '\r':
                                $utf8 .= chr(0x0D);
                                ++$c;
                                break;

                            case $substr_chrs_c_2 == '\\"':
                            case $substr_chrs_c_2 == '\\\'':
                            case $substr_chrs_c_2 == '\\\\':
                            case $substr_chrs_c_2 == '\\/':
                                if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                   ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
                                    $utf8 .= $chrs{++$c};
                                }
                                break;

                            case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                // single, escaped unicode character
                                $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                       . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                $utf8 .= $this->utf162utf8($utf16);
                                $c += 5;
                                break;

                            case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                $utf8 .= $chrs{$c};
                                break;

                            case ($ord_chrs_c & 0xE0) == 0xC0:
                                // characters U-00000080 - U-000007FF, mask 110XXXXX
                                $utf8 .= substr($chrs, $c, 2);
                                ++$c;
                                break;

                            case ($ord_chrs_c & 0xF0) == 0xE0:
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                $utf8 .= substr($chrs, $c, 3);
                                $c += 2;
                                break;

                            case ($ord_chrs_c & 0xF8) == 0xF0:
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                $utf8 .= substr($chrs, $c, 4);
                                $c += 3;
                                break;

                            case ($ord_chrs_c & 0xFC) == 0xF8:
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                $utf8 .= substr($chrs, $c, 5);
                                $c += 4;
                                break;

                            case ($ord_chrs_c & 0xFE) == 0xFC:
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                $utf8 .= substr($chrs, $c, 6);
                                $c += 5;
                                break;
                        }

                    }

                    return $utf8;

                } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                    if ($str{0} == '[') {
                        $stk = array(3);
                        $arr = array();
                    } else {
                        $stk = array(4);
                        $obj = array();
                    }

                    array_push($stk, array('what'  => 1,
                                           'where' => 0,
                                           'delim' => false));

                    $chrs = substr($str, 1, -1);
                    $chrs = preg_replace(array('#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us'), '', $chrs);
                    $chrs = trim($chrs);

                    if ($chrs == '') {
                        if (reset($stk) == 3) {
                            return $arr;
                        } else {
                            return $obj;
                        }
                    }

                    $strlen_chrs = strlen($chrs);

                    for ($c = 0; $c <= $strlen_chrs; ++$c) {

                        $top = end($stk);
                        $substr_chrs_c_2 = substr($chrs, $c, 2);

                        if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == 1))) {
                            $slice = substr($chrs, $top['where'], ($c - $top['where']));
                            array_push($stk, array('what' => 1, 'where' => ($c + 1), 'delim' => false));

                            if (reset($stk) == 3) {
                                array_push($arr, Jaws_UTF8::json_decode($slice));

                            } elseif (reset($stk) == 4) {
                                $parts = array();

                                if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    $key = Jaws_UTF8::json_decode($parts[1]);
                                    $val = Jaws_UTF8::json_decode($parts[2]);
                                    $obj[$key] = $val;
                                } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                    $key = $parts[1];
                                    $val = Jaws_UTF8::json_decode($parts[2]);
                                    $obj[$key] = $val;
                                }

                            }

                        } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != 2)) {
                            array_push($stk, array('what' => 2, 'where' => $c, 'delim' => $chrs{$c}));

                        } elseif (($chrs{$c} == $top['delim']) &&
                                 ($top['what'] == 2) &&
                                 ((strlen(substr($chrs, 0, $c)) - strlen(rtrim(substr($chrs, 0, $c), '\\'))) % 2 != 1)) {
                            array_pop($stk);

                        } elseif (($chrs{$c} == '[') &&
                                 in_array($top['what'], array(1, 3, 4))) {
                            array_push($stk, array('what' => 3, 'where' => $c, 'delim' => false));

                        } elseif (($chrs{$c} == ']') && ($top['what'] == 3)) {
                            array_pop($stk);

                        } elseif (($chrs{$c} == '{') &&
                                 in_array($top['what'], array(1, 3, 4))) {
                            array_push($stk, array('what' => 4, 'where' => $c, 'delim' => false));

                        } elseif (($chrs{$c} == '}') && ($top['what'] == 4)) {
                            array_pop($stk);

                        } elseif (($substr_chrs_c_2 == '/*') &&
                                 in_array($top['what'], array(1, 3, 4))) {
                            array_push($stk, array('what' => 5, 'where' => $c, 'delim' => false));
                            $c++;

                        } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == 5)) {
                            array_pop($stk);
                            $c++;

                            for ($i = $top['where']; $i <= $c; ++$i)
                                $chrs = substr_replace($chrs, ' ', $i, 1);
                        }
                    }

                    if (reset($stk) == 3) {
                        return $arr;
                    } elseif (reset($stk) == 4) {
                        return $obj;
                    }
                }
        }
    }

}