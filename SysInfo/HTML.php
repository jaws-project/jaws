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
class SysInfoHTML extends Jaws_GadgetHTML
{
    /**
     * Default Action
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
     */
    function DefaultAction()
    {
        return $this->SysInfo();
    }

    /**
     * System Information
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
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
     * PHP Settings
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
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
     * Jaws Settings
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
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
     * Directory Permissions
     *
     * @access  public
     * @return  string  HTML content of DefaultAction
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