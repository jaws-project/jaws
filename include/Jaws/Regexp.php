<?php
/**
 * A support class for regular expressions methods
 *
 * @category    Regexp
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Regexp
{
    /**
     * The regexp pattern
     *
     * @var string
     */
    private $pattern;

    /**
     * The matches from the last method called
     *
     * @var array;
     */
    public $matches;

    /**
     * Constructor
     *
     * @param   string  $pattern
     *
     * @return  Jaws_Regexp
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Quotes regular expression characters
     *
     * @param   string  $str    The input string
     *
     * @return  string  Returns the quoted (escaped) string
     */
    public function quote($str)
    {
        return preg_quote($str, '/');
    }

    /**
     * Matches the given string. Matches all the same way as Ruby's scan method
     *
     * @param string $subject
     *
     * @return  Returns the number of full pattern matches, or FALSE if an error occurred
     */
    public function scan($subject)
    {
        $this->matches = array();
        $result = preg_match_all($this->pattern, $subject, $matches, PREG_SET_ORDER);
        if (!empty($result)) {
            foreach ($matches as $key => $match) {
                array_shift($match);
                $this->matches[$key] = $match;
            }
        }

        return $result;
    }

    /**
     * Matches the given string. Only matches once.
     *
     * @param string $subject
     *
     * @return int 1 if there was a match, 0 if there wasn't
     */
    public function match($subject)
    {
        return preg_match($this->pattern, $subject, $this->matches);
    }

    /**
     * Matches the given string. Matches all.
     *
     * @param   string  $subject
     *
     * @return int The number of matches
     */
    public function matchAll($subject)
    {
        return preg_match_all($this->pattern, $subject, $this->matches);
    }

    /**
     * Splits the given string
     *
     * @param   string  $subject    The input string
     * @param   int     $limit      Limits the amount of results returned
     * @param   int     $flags
     *
     * @return  array|bool  Array containing substrings of splitted subject, or FALSE on failure
     */
    public function split($subject, $limit = -1, int $flags = 0)
    {
        return preg_split($this->pattern, $subject, $limit, $flags);
    }

}
