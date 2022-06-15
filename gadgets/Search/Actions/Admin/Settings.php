<?php
/**
 * Search Gadget Admin
 *
 * @category    Gadget Admin
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Actions_Admin_Settings extends Jaws_Gadget_Action
{
    /**
     * Displays gadget administration section
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Settings()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Search.html');
        $tpl->SetBlock('admin');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('settings', Jaws::t('SETTINGS'));

        $model = $this->gadget->model->load('Search');
        $gadgetList = $model->GetSearchableGadgets();
        $gSearchable = $this->gadget->registry->fetch('searchable_gadgets');
        $searchableGadgets = ($gSearchable=='*')? array_keys($gadgetList) : explode(', ', $gSearchable);

        if (count($gadgetList) > 0) {
            $gchk =& Piwi::CreateWidget('CheckButtons', 'gadgets', 'vertical');
            foreach ($gadgetList as $tgadget) {
                if ($tgadget['name'] != 'Search') {
                    if (in_array($tgadget['name'], $searchableGadgets)) {
                        $gchk->AddOption($tgadget['title'], $tgadget['name'], null, true);
                    } else {
                        $gchk->AddOption($tgadget['title'], $tgadget['name'], null, false);
                    }
                }
            }
            $tpl->SetVariable('selected_gadgets', $gchk->Get());
        } else {
            $tpl->SetVariable('selected_gadgets', '');
        }

        $usewithCombo =& Piwi::CreateWidget('Combo', 'use_with');
        $usewithCombo->AddOption($this::t('ALWAYS'), 'always');
        $usewithCombo->AddOption($this::t('ONLY_IN_GADGET'), 'selected');
        $usewithCombo->AddEvent(ON_CHANGE, 'javascript:show_gadgets();');
        if ($gSearchable == '*') {
            $usewithCombo->SetDefault('always');
            $tpl->SetVariable('display', 'none');
        } else {
            $usewithCombo->SetDefault('selected');
            $tpl->SetVariable('display', 'block');
        }

        $saveButton =& Piwi::CreateWidget('Button', 'Save', Jaws::t('SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript:saveChanges(this.form);');

        $tpl->SetVariable('save_button', $saveButton->Get());
        $tpl->SetVariable('use_with', $this::t('USE_WITH'));
        $tpl->SetVariable('use_with_combo', $usewithCombo->Get());

        $tpl->ParseBlock('admin');
        return $tpl->Get();
    }
}