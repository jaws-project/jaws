<?php
/**
 * Layout Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Admin_When extends Jaws_Gadget_Action
{
    /**
     * Changes when to display a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function ChangeDisplayWhen()
    {
        $model = $this->gadget->model->loadAdmin('Elements');

        $tpl = $this->gadget->template->loadAdmin('DisplayWhen.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('display_when', _t('LAYOUT_DISPLAY'));

        $id = jaws()->request->fetch('id', 'get');
        $layoutElement = $model->GetElement($id);
        if (is_array($layoutElement) && !empty($layoutElement)) {
            $dw_value = $layoutElement['display_when'];
        }

        $displayCombo =& Piwi::CreateWidget('Combo', 'display_in');
        $displayCombo->AddOption(_t('LAYOUT_ALWAYS'), 'always');
        $displayCombo->AddOption(_t('LAYOUT_ONLY_IN_GADGET'), 'selected');

        if ($dw_value == '*') {
            $displayCombo->SetDefault('always');
            $tpl->SetVariable('selected_display', 'none');
        } else {
            $displayCombo->SetDefault('selected');
            $tpl->SetVariable('selected_display', 'block');
        }
        $displayCombo->AddEvent(ON_CHANGE, "showGadgets();");
        $tpl->SetVariable('display_in_combo', $displayCombo->Get());

        // Display in list
        $selectedGadgets = explode(',', $dw_value);
        // for index...
        $gadget_field =& Piwi::CreateWidget('CheckButtons', 'checkbox_index', 'vertical');
        $gadget_field->AddOption(_t('LAYOUT_INDEX'), 'index', null, in_array('index', $selectedGadgets));
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadget_list = $cmpModel->GetGadgetsList(null, true, true, true);
        foreach ($gadget_list as $g) {
            $gadget_field->AddOption($g['title'], $g['name'], null, in_array($g['name'], $selectedGadgets));
        }
        $tpl->SetVariable('selected_gadgets', $gadget_field->Get());

        $saveButton =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "parent.parent.saveChangeDW(".$id.", getSelectedGadgets());");
        $tpl->SetVariable('save', $saveButton->Get());

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

}