<?php
/**
 * Jaws GZip-File Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2022-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_GZFile
{
    /**
     * Returns gzipped raw data as file
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns gzipped raw data as file
     */
    static function get($data)
    {
        $data = gzencode($data, COMPRESS_LEVEL, FORCE_GZIP);

        header('Content-Type: application/octet-stream');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: '.strlen($data));
        header('Content-Encoding: gzip');

        return $data;
    }

}