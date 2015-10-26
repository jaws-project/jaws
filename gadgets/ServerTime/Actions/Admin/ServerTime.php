<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
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
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'ServerTime'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet($this->gadget->title);
        $fieldset->SetDirection('vertical');
        $fieldset->SetStyle('white-space: nowrap;');

        $now = time();
        $objDate = Jaws_Date::getInstance();
        $dFormat =& Piwi::CreateWidget('Combo', 'date_format');
        $dFormat->SetID('date_format');
        $dFormat->SetTitle(_t('SERVERTIME_FORMAT_TEXT'));
        $dFormat->SetStyle('width: 300px;');
        $dFormat->AddOption($objDate->Format($now, 'MN j, g:i a'),     'MN j, g:i a');
        $dFormat->AddOption($objDate->Format($now, 'j.m.y'),           'j.m.y');
        $dFormat->AddOption($objDate->Format($now, 'j MN, g:i a'),     'j MN, g:i a');
        $dFormat->AddOption($objDate->Format($now, 'y.m.d, g:i a'),    'y.m.d, g:i a');
        $dFormat->AddOption($objDate->Format($now, 'd MN Y'),          'd MN Y');
        $dFormat->AddOption($objDate->Format($now, 'DN d MN Y'),       'DN d MN Y');
        $dFormat->AddOption($objDate->Format($now, 'DN d MN Y g:i a'), 'DN d MN Y g:i a');
        $dFormat->AddOption($objDate->Format($now, 'j MN y'),          'j MN y');
        $dFormat->SetDefault($this->gadget->registry->fetch('date_format'));
        $fieldset->Add($dFormat);

        $form->Add($fieldset);
        $submit =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_UPDATE', _t('GLOBAL_SETTINGS')), STOCK_SAVE);
        $submit->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $submit->AddEvent(ON_CLICK, 'javascript:updateProperties(this.form);');
        $form->Add($submit);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('servertime');
        return $tpl->Get();
    }

}