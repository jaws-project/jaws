<?php
/**
 * Jaws JSON Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_JSON
{
    /**
     * Returns json-encoded data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        return json_encode($data);
    }

}