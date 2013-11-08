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
class SysInfo_Actions_JawsInfo extends Jaws_Gadget_Action
{
    /**
     * Displays information around your Jaws like installed gadgets, plugins, ...
     *
     * @access  public
     * @return  string XHTML template content
     */
    function JawsInfo()
    {
        if (!$GLOBALS['app']->Session->GetPermission('SysInfo', 'JawsInfo')) {
            return false;
        }

        $model = $this->gadget->model->load('JawsInfo');
        $tpl = $this->gadget->loadTemplate('SysInfo.html');
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
}