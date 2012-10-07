<?php
/**
 * SysInfo Gadget (layout actions for client side)
 *
 * @category   Gadget
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfoLayoutHTML 
{
    /**
     * Show information around system(OS, WebServer, Database,...)
     *
     * @access public
     * @return string template content
     */
    function SysInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'SysInfo')) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('SysInfo', 'Model');
        $tpl = new Jaws_Template('gadgets/SysInfo/templates/');
        $tpl->Load('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('title', _t('SYSINFO_SYSINFO'));

        //System Information
        $tpl->SetBlock('SysInfo/InfoSection');
        $items = $model->GetSysInfo();
        foreach ($items as $item) {
            $tpl->SetBlock('SysInfo/InfoSection/InfoItem');
            $tpl->SetVariable('item_title', $item['title']);
            $tpl->SetVariable('item_value', $item['value']);
            $tpl->ParseBlock('SysInfo/InfoSection/InfoItem');
        }
        $tpl->ParseBlock('SysInfo/InfoSection');

        $tpl->ParseBlock('SysInfo');
        return $tpl->Get();
    }

    /**
     * Show some common PHP settings like memory limit, safe mode, ...
     *
     * @access public
     * @return string template content
     */
    function PHPInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'PHPInfo')) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('SysInfo', 'Model');
        $tpl = new Jaws_Template('gadgets/SysInfo/templates/');
        $tpl->Load('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('title',  _t('SYSINFO_PHPINFO'));

        //PHP Settings
        $tpl->SetBlock('SysInfo/InfoSection');
        $items = $model->GetPHPInfo();
        foreach ($items as $item) {
            $tpl->SetBlock('SysInfo/InfoSection/InfoItem');
            $tpl->SetVariable('item_title', $item['title']);
            $tpl->SetVariable('item_value', $item['value']);
            $tpl->ParseBlock('SysInfo/InfoSection/InfoItem');
        }
        $tpl->ParseBlock('SysInfo/InfoSection');

        $tpl->ParseBlock('SysInfo');
        return $tpl->Get();
    }

    /**
     * Show information around your Jaws like installed gadgets, plugins, ...
     *
     * @access public
     * @return string template content
     */
    function JawsInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'JawsInfo')) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('SysInfo', 'Model');
        $tpl = new Jaws_Template('gadgets/SysInfo/templates/');
        $tpl->Load('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('title',  _t('SYSINFO_JAWSINFO'));

        //Jaws Settings
        $tpl->SetBlock('SysInfo/InfoSection');
        $items = $model->GetJawsInfo();
        foreach ($items as $item) {
            $tpl->SetBlock('SysInfo/InfoSection/InfoItem');
            $tpl->SetVariable('item_title', $item['title']);
            $tpl->SetVariable('item_value', $item['value']);
            $tpl->ParseBlock('SysInfo/InfoSection/InfoItem');
        }
        $tpl->ParseBlock('SysInfo/InfoSection');

        $tpl->ParseBlock('SysInfo');
        return $tpl->Get();
    }

    /**
     * Show information about Jaws's main directories like permissions, ...
     *
     * @access public
     * @return string template content
     */
    function DirInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'DirInfo')) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('SysInfo', 'Model');
        $tpl = new Jaws_Template('gadgets/SysInfo/templates/');
        $tpl->Load('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('title',  _t('SYSINFO_DIRINFO'));

        //Directory Permissions
        $tpl->SetBlock('SysInfo/InfoSection');
        $items = $model->GetDirsPermissions();
        foreach ($items as $item) {
            $tpl->SetBlock('SysInfo/InfoSection/InfoItem');
            $tpl->SetVariable('item_title', $item['title']);
            $tpl->SetVariable('item_value', $item['value']);
            $tpl->ParseBlock('SysInfo/InfoSection/InfoItem');
        }
        $tpl->ParseBlock('SysInfo/InfoSection');

        $tpl->ParseBlock('SysInfo');
        return $tpl->Get();
    }

}