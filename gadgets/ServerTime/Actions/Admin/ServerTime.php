<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_Actions_Admin_ServerTime extends Jaws_Gadget_Action
{
    /**
     * Displays the administration page
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('ServerTime.html');
        $tpl->SetBlock('servertime');

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqGadget', 'ServerTime'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqAction', 'UpdateProperties'));

        include_once ROOT_JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet($this->gadget->title);
        $fieldset->SetDirection('vertical');
        $fieldset->SetStyle('white-space: nowrap;');

        $now = time();
        $objDate = Jaws_Date::getInstance();
        $dFormat =& Piwi::CreateWidget('Combo', 'date_format');
        $dFormat->SetID('date_format');
        $dFormat->SetTitle($this::t('FORMAT_TEXT'));
        $dFormat->SetStyle('width: 300px;');
        $dFormat->AddOption($objDate->Format($now, 'MMMM d, h:mm aa'), 'MMMM d, h:mm aa');
        $dFormat->AddOption($objDate->Format($now, 'd.m.yy'), 'd.m.yy');
        $dFormat->AddOption($objDate->Format($now, 'd MMMM, h:mm aa'), 'd MMMM, h:mm aa');
        $dFormat->AddOption($objDate->Format($now, 'yy.m.dd, h:mm aa'), 'yy.m.dd, h:mm aa');
        $dFormat->AddOption($objDate->Format($now, 'dd MMMM yyyy'), 'dd MMMM yyyy');
        $dFormat->AddOption($objDate->Format($now, 'EEEE dd MMMM yyyy'), 'EEEE dd MMMM yyyy');
        $dFormat->AddOption($objDate->Format($now, 'EEEE dd MMMM yyyy h:mm aa'), 'EEEE dd MMMM yyyy h:mm aa');
        $dFormat->AddOption($objDate->Format($now, 'd MMMM yy'), 'd MMMM yy');
        $dFormat->SetDefault($this->gadget->registry->fetch('date_format'));
        $fieldset->Add($dFormat);

        $form->Add($fieldset);
        $submit =& Piwi::CreateWidget('Button', 'save', Jaws::t('UPDATE', Jaws::t('SETTINGS')), STOCK_SAVE);
        $submit->SetStyle(Jaws::t('LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $submit->AddEvent(ON_CLICK, 'javascript:updateProperties(this.form);');
        $form->Add($submit);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('servertime');
        return $tpl->Get();
    }

}