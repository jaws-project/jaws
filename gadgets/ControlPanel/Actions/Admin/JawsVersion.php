<?php
/**
 * ControlPanel Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_JawsVersion extends Jaws_Gadget_HTML
{
    /**
     * Returns latest jaws version
     *
     * @access  public
     * @return  string  Json encoded string
     */
    function JawsVersion()
    {
        // Set Headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        return Jaws_UTF8::json_encode(JAWS_VERSION);
    }

}