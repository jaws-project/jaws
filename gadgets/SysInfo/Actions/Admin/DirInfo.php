<?php
/**
 * SysInfo Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Actions_Admin_DirInfo extends SysInfo_Actions_Admin_Default
{
    /**
     * Returns directory permissions
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DirInfo()
    {
        $this->gadget->CheckPermission('DirInfo');
        $model = $this->gadget->model->load('DirInfo');
        $tpl = $this->gadget->template->loadAdmin('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('sidebar', $this->SideBar('DirInfo'));

        //Directory Permissions
        $tpl->SetBlock('SysInfo/InfoSection');
        $tpl->SetVariable('section_title', _t('SYSINFO_DIRINFO'));
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