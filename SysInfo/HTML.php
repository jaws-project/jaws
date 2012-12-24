<?php
/**
 * SysInfo Core Gadget
 *
 * @category   Gadget
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
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
        return $this->SysInfo();
    }

    /**
     * Gets system information
     *
     * @access  public
     * @return  string  XHTML content
     */
    function SysInfo()
    {
        $this->SetTitle(_t('SYSINFO_SYSINFO'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('SysInfo', 'LayoutHTML');
        $result = $layoutGadget->SysInfo();
        if ($result) {
            return $result;
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
    }

    /**
     * Gets PHP settings of the server
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PHPInfo()
    {
        $this->SetTitle(_t('SYSINFO_PHPINFO'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('SysInfo', 'LayoutHTML');
        $result = $layoutGadget->PHPInfo();
        if ($result) {
            return $result;
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
    }

    /**
     * Gets installed Jaws specifications
     *
     * @access  public
     * @return  string  XHTML content
     */
    function JawsInfo()
    {
        $this->SetTitle(_t('SYSINFO_JAWSINFO'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('SysInfo', 'LayoutHTML');
        $result = $layoutGadget->JawsInfo();
        if ($result) {
            return $result;
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
    }

    /**
     * Gets permissions of Jaws directories
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DirInfo()
    {
        $this->SetTitle(_t('SYSINFO_DIRINFO'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('SysInfo', 'LayoutHTML');
        $result = $layoutGadget->DirInfo();
        if ($result) {
            return $result;
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }
    }

}