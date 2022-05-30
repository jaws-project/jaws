<?php
/**
 * Jaws GZip-JSON Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_GZJSON
{
    /**
     * Returns gzip-ed json-encoded data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        $data = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_HEX_APOS);
        $data = gzencode($data, COMPRESS_LEVEL, FORCE_GZIP);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: '.strlen($data));
        header('Content-Encoding: gzip');

        return $data;
    }

}