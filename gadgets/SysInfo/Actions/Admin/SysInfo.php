<?php
require_once JAWS_PATH. 'gadgets/SysInfo/Actions/Admin/Default.php';
/**
 * SysInfo Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Actions_Admin_SysInfo extends SysInfo_Actions_Admin_Default
{
    /**
     * Returns information around system(OS, WebServer, Database,...)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function SysInfo()
    {
        $this->gadget->CheckPermission('SysInfo');
        $model = $this->gadget->loadModel('SysInfo');
        $tpl = $this->gadget->loadAdminTemplate('SysInfo.html');
        $tpl->SetBlock('SysInfo');
        $tpl->SetVariable('sidebar', $this->SideBar('SysInfo'));

        //System Information
        $tpl->SetBlock('SysInfo/InfoSection');
        $tpl->SetVariable('section_title', _t('SYSINFO_SYSINFO'));
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
}