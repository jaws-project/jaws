<?php
/**
 * Jaws Extended JSON Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_XJSON
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

        return json_encode(
            array(
                'defines' => Jaws::getInstance()->defines(),
                'content' => $data,
            ),
            JSON_PARTIAL_OUTPUT_ON_ERROR
        );
    }

}