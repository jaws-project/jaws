<?php
/**
 * Layout Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_DisplayWhen extends Jaws_Gadget_Action
{
    /**
     * Changes when to display a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function DisplayWhen()
    {
        $rqst = $this->gadget->request->fetch(array('id', 'layout'), 'get');
        $layout = empty($rqst['layout'])? 'Layout' : $rqst['layout'];

        // check permissions
        if ($layout == 'Index.Dashboard') {
            $this->app->session->checkPermission('Users', 'ManageDashboard');
        } else {
            $this->app->session->checkPermission('Users', 'ManageLayout');
        }

        $model = $this->gadget->model->loadAdmin('Elements');
        $tpl = $this->gadget->template->load('DisplayWhen.html');
        $tpl->SetBlock('template');

        $direction = Jaws::t('LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $this->app->getBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $this->app->getSiteURL('/');

        $tpl->SetVariable('base_url', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('lbl_when', $this::t('DISPLAY'));

        $layoutElement = $model->GetElement($rqst['id']);
        if (is_array($layoutElement) && !empty($layoutElement)) {
            $dw_value = $layoutElement['when'];
        }

        $displayCombo =& Piwi::CreateWidget('Combo', 'display_in');
        $displayCombo->AddOption($this::t('ALWAYS'), 'always');
        $displayCombo->AddOption($this::t('ONLY_IN_GADGET'), 'selected');

        if ($dw_value == '*') {
            $displayCombo->SetDefault('always');
            $tpl->SetVariable('selected_display', 'none');
        } else {
            $displayCombo->SetDefault('selected');
            $tpl->SetVariable('selected_display', 'block');
        }
        $displayCombo->AddEvent(ON_CHANGE, "parent.parent.showGadgets(document);");
        $tpl->SetVariable('display_in_combo', $displayCombo->Get());

        // Display in list
        $selectedGadgets = explode(',', $dw_value);
        // for index...
        $gadget_field =& Piwi::CreateWidget('CheckButtons', 'checkbox_index', 'vertical');
        $gadget_field->AddOption($this::t('INDEX'), 'index', null, in_array('index', $selectedGadgets));
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadget_list = $cmpModel->GetGadgetsList(null, true, true, true);
        foreach ($gadget_list as $g) {
            $gadget_field->AddOption($g['title'], $g['name'], null, in_array($g['name'], $selectedGadgets));
        }
        $tpl->SetVariable('selected_gadgets', $gadget_field->Get());

        $saveButton =& Piwi::CreateWidget('Button', 'ok',Jaws::t('SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(
            ON_CLICK,
            "parent.parent.saveChangeDW(".$layoutElement['id'].", parent.parent.getSelectedGadgets(document));"
        );
        $tpl->SetVariable('save', $saveButton->Get());

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

    /**
     * Change when to display a gadget
     * 
     * @access  public
     * @return  array   Response
     */
    function UpdateDisplayWhen() 
    {
        @list($item, $layout, $dw) = $this->gadget->request->fetchAll('post');
        // check permissions
        if ($layout == 'Index.Dashboard') {
            $this->app->session->checkPermission('Users', 'ManageDashboard');
        } else {
            $this->app->session->checkPermission('Users', 'ManageLayout');
        }

        $model = $this->gadget->model->loadAdmin('Elements');
        $res = $model->UpdateDisplayWhen($item, $layout, $dw);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($this::t('ERROR_CHANGE_WHEN'), RESPONSE_ERROR);
        } else {
            $this->gadget->session->push($this::t('ELEMENT_CHANGE_WHEN'), RESPONSE_NOTICE);
        }
        return $this->gadget->session->pop();
    }

}