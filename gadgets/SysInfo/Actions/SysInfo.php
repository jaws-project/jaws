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
class SysInfo_Actions_SysInfo extends Jaws_Gadget_Action
{
    /**
     * Displays information around system(OS, WebServer, Database,...)
     *
     * @access  public
     * @return  string XHTML template content
     */
    function SysInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'SysInfo')) {
            return false;
        }

        $model = $this->gadget->model->load('SysInfo');
        $tpl = $this->gadget->loadTemplate('SysInfo.html');
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

}