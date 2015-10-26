<?php
/**
 * Tags Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Tags
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tags_Actions_Admin_Settings extends Tags_Actions_Admin_Default
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->gadget->CheckPermission('Settings');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('Settings');

        // view tag result limit
        $limit = (int)$this->gadget->registry->fetch('tag_results_limit');
        $limitCombo =& Piwi::CreateWidget('Combo', 'tag_results_limit');
        $limitCombo->setContainerClass('oneline');
        $limitCombo->SetTitle(_t('BLOG_LAST_ENTRIES_LIMIT'));
        $limitCombo->AddOption(5, 5);
        $limitCombo->AddOption(10, 10);
        $limitCombo->AddOption(15, 15);
        $limitCombo->AddOption(20, 20);
        $limitCombo->AddOption(30, 30);
        $limitCombo->AddOption(50, 50);
        $limitCombo->SetDefault(($limit>0) ? $limit : 10);
        $tpl->SetVariable('lbl_tag_results_limit', _t('TAGS_RESULT_LIMIT'));
        $tpl->SetVariable('tag_results_limit', $limitCombo->Get());

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:saveSettings();');
        $tpl->SetVariable('btn_save', $save->Get());

        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $tpl->ParseBlock('Settings');

        return $tpl->Get();
    }

}