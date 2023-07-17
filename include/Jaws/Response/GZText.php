<?php
/**
 * Jaws GZip-Text Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_GZText
{
    /**
     * Returns gzipped raw data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns gzipped raw data
     */
    static function get($data)
    {
        $data = gzencode($data, COMPRESS_LEVEL, FORCE_GZIP);

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: '.strlen($data));
        header('Content-Encoding: gzip');

        return $data;
    }

}