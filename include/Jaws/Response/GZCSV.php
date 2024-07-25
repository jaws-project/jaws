<?php
/**
 * Jaws GZip-CSV Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_GZCSV
{
    /**
     * Returns gz-csv encoded data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        $result = "\xEF\xBB\xBF";
        if (is_array($data)) {
            foreach ($data as $entry) {
                $result.= str_putcsv($entry). "\n";
            }
        } else {
            $result.= $data;
        }
        $data = gzencode($result, COMPRESS_LEVEL, FORCE_GZIP);

        header('Content-Type: text/csv; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: '.strlen($data));
        header('Content-Encoding: gzip');

        return $data;
    }

}