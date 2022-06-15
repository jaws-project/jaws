<?php
/**
 * This class represents the entire template document
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Document extends Jaws_XTemplate_TagSegmental
{
    /**
     * Constructor.
     *
     * @param array $tokens
     */
    public function __construct(array &$tokens, $markup = '')
    {
        $this->parse($tokens);
    }

    /**
     * Check for cached includes; if there are - do not use cache
     *
     * @return bool if need to discard cache
     */
    public function hasIncludes()
    {
        $seenExtends = false;
        $seenBlock = false;

        foreach ($this->nodelist as $token) {
            if ($token instanceof Jaws_XTemplate_Tags_Extends) {
                $seenExtends = true;
            } elseif ($token instanceof Jaws_XTemplate_Tags_Block) {
                $seenBlock = true;
            }
        }

        /*
         * We try to keep the base templates in cache (that not extend anything).
         *
         * At the same time if we re-render all other blocks we see, we avoid most
         * if not all related caching quirks. This may be suboptimal.
         */
        if ($seenBlock && !$seenExtends) {
            return true;
        }

        foreach ($this->nodelist as $token) {
            // check any of the tokens for includes
            if ($token instanceof Jaws_XTemplate_Tags_Include && $token->hasIncludes()) {
                return true;
            }

            if ($token instanceof Jaws_XTemplate_Tags_Extends && $token->hasIncludes()) {
                return true;
            }
        }

        return false;
    }

    /**
     * There isn't a real delimiter
     *
     * @return string
     */
    protected function blockDelimiter()
    {
        return '';
    }

    /**
     * Document blocks don't need to be terminated since they are not actually opened
     */
    protected function assertMissingDelimitation()
    {
    }

}