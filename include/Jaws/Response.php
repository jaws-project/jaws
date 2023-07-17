<?php
/**
 * Jaws Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2022 Jaws Development Group
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
        $resType = preg_replace('/[^[:alnum:]_\-]/', '', $resType);
        $drivers = array_map('basename', glob(ROOT_JAWS_PATH . 'include/Jaws/Response/*.php'));
        if (false !== $driver = array_search(strtolower("$resType.php"), array_map('strtolower', $drivers))) {
            $resType = basename($drivers[$driver], '.php');
        } else {
            $resType = 'Text';
        }

        $resTypeFile = ROOT_JAWS_PATH . "include/Jaws/Response/$resType.php";
        if (!file_exists($resTypeFile)) {
            return $data;
        }

        $className = 'Jaws_Response_' . $resType;
        return $className::get($data);
    }

}