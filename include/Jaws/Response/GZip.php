<?php
/**
 * Jaws GZip Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_GZip
{
    /**
     * Returns gzip-ed data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns gzip-ed data
     */
    static function get($data)
    {
        $data = gzencode($data, COMPRESS_LEVEL, FORCE_GZIP);
        header('Content-Length: '.strlen($data));
        header('Content-Encoding: gzip');
        return $data;
    }

}