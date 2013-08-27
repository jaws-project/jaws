<?php
/**
 * SysInfo Core Gadget
 *
 * @category   Gadget
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_HTML extends Jaws_Gadget_HTML
{
    /**
     * Gets system information
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        $HTML = $GLOBALS['app']->LoadGadget('SysInfo', 'HTML', 'SysInfo');
        return $HTML->SysInfo();
    }
}