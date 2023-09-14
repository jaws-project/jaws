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
     * Parse the given tokens.
     *
     * @param   array   $tokens
     */
    public function parse(array &$tokens, $process = false)
    {
        if ($process) {
            parent::parse($tokens);
        }
        // Do nothing by default
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