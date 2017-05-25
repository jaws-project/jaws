<?php
/**
 * Jaws Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response
{
    /**
     * An interface for available response drivers
     *
     * @access  public
     * @param   string  $resType    Response type
     * @param   string  $data       Data string
     * @return  string  Return data
     */
    static function get($resType, $data)
    {
        switch ($resType) {
            case 'gzip':
            case 'x-gzip':
                $resType = 'GZip';
                break;

            case 'json':
                $resType = 'JSON';
                break;
        }

        $resType = preg_replace('/[^[:alnum:]_-]/', '', $resType);
        $resTypeFile = JAWS_PATH . "include/Jaws/Response/$resType.php";
        if (!file_exists($resTypeFile)) {
            return $data;
        }

        $className = 'Jaws_Response_' . $resType;
        return $className::get($data);
    }

}