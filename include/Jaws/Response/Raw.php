<?php
/**
 * Jaws Raw Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2022-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_Raw
{
    /**
     * Returns raw data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns raw data
     */
    static function get($data)
    {
        return $data;
    }

}