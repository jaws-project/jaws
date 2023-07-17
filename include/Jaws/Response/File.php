<?php
/**
 * Jaws File Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2022-2023 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_File
{
    /**
     * Returns raw data as file
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns raw data
     */
    static function get($data)
    {
        header('Content-Type: application/octet-stream');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        return $data;
    }

}