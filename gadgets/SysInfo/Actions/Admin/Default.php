<?php
/**
 * SysInfo Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Calls default action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        if ($this->gadget->GetPermission('SysInfo')) {
            $HTML = $this->gadget->action->loadAdmin('SysInfo');
            return $HTML->SysInfo();
        } elseif ($this->gadget->GetPermission('PHPInfo')) {
            $HTML = $this->gadget->action->loadAdmin('PHPInfo');
            return $HTML->PHPInfo();
        } elseif ($this->gadget->GetPermission('JawsInfo')) {
            $HTML = $this->gadget->action->loadAdmin('JawsInfo');
            return $HTML->JawsInfo();
        }

        $HTML = $this->gadget->action->loadAdmin('DirInfo');
        $this->gadget->CheckPermission('DirInfo');
        return $HTML->DirInfo();
    }

    /**
     * Displays the sidebar
     *
     * @access  public
     * @param   string  $action Selected Action
     * @return  string  XHTML sidebar
     */
    function SideBar($action)
    {
        $actions = array('SysInfo', 'PHPInfo', 'JawsInfo', 'DirInfo');
        if (!in_array($action, $actions)) {
            $action = 'SysInfo';
        }

        $sidebar = new Jaws_Widgets_Sidebar('sysinfo');
        if ($this->gadget->GetPermission('SysInfo')) {
            $sidebar->AddOption('SysInfo', _t('SYSINFO_SYSINFO'), 
                                BASE_SCRIPT . '?gadget=SysInfo&amp;action=SysInfo',
                                'gadgets/SysInfo/Resources/images/sysinfo.png');
        }

        if ($this->gadget->GetPermission('PHPInfo')) {
            $sidebar->AddOption('PHPInfo', _t('SYSINFO_PHPINFO'),
                                BASE_SCRIPT . '?gadget=SysInfo&amp;action=PHPInfo',
                                'gadgets/SysInfo/Resources/images/phpinfo.png');
        }

        if ($this->gadget->GetPermission('JawsInfo')) {
            $sidebar->AddOption('JawsInfo', _t('SYSINFO_JAWSINFO'),
                                BASE_SCRIPT . '?gadget=SysInfo&amp;action=JawsInfo',
                                'gadgets/SysInfo/Resources/images/jawsinfo.png');
        }

        if ($this->gadget->GetPermission('DirInfo')) {
            $sidebar->AddOption('DirInfo', _t('SYSINFO_DIRINFO'),
                                BASE_SCRIPT . '?gadget=SysInfo&amp;action=DirInfo',
                                'gadgets/SysInfo/Resources/images/dirinfo.png');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }
}