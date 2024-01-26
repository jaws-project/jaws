<?php
/**
 * Jaws CSV Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_CSV
{
    /**
     * Returns csv encoded data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        $result = "\xEF\xBB\xBF";
        foreach ($data as $entry) {
            $result.= str_putcsv($entry). "\n";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        return $result;
    }

}