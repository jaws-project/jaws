<?php
/**
 * Glossary Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Actions_Admin_Terms extends Jaws_Gadget_Action
{
    /**
     * Manages the main functions of Glossary administration
     *
     * @access  public
     * @return  stirng  XHTML template Content
     */
    function Terms()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Glossary.html');
        $tpl->SetBlock('Glossary');

        // Block List
        $model = $this->gadget->model->load('Term');
        $terms = $model->GetTerms();
        $termsCombo =& Piwi::CreateWidget('Combo', 'term_id');
        $termsCombo->SetID('term_id');
        $termsCombo->SetStyle('width: 100%; margin-bottom: 10px;');
        $termsCombo->SetSize(25);
        $termsCombo->AddEvent(ON_CHANGE, 'edit(this.value, \'' . Jaws::t('EDIT') . '\');');
        foreach ($terms as $term) {
            if (!isset($selected_content)) {
                $selected_content = $term['description'];
            }
            $termsCombo->AddOption($term['term'], $term['id']);
        }
        $termsCombo->SetDefault(0);
        $tpl->SetVariable('term_list', $termsCombo->Get());

        // New Button
        if ($this->gadget->GetPermission('AddTerm')) {
            $newButton =& Piwi::CreateWidget('Button', 'newButton', Jaws::t('CREATE', $this::t('TERM')), STOCK_NEW);
            $newButton->AddEvent(ON_CLICK, 'createNewTerm(\'' . Jaws::t('CREATE', $this::t('TERM')) . '\');');
            $newButton->SetID('newButton');
            $tpl->SetVariable('new_button', $newButton->Get());
        } else {
            $tpl->SetVariable('new_button', '');
        }

        // Tabs titles
        $tpl->SetVariable('edit', Jaws::t('EDIT'));
        $tpl->SetVariable('preview', Jaws::t('PREVIEW'));
        // Edit form
        $idHidden =& Piwi::CreateWidget('HiddenEntry', 'id');
        $idHidden->SetID('hidden_id');
        $tpl->SetVariable('hidden_id', $idHidden->Get());

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetID('term_title');
        $title->SetStyle('width: 256px;');
        $tpl->SetVariable('term_title', $this::t('TERM'));
        $tpl->SetVariable('title_field', $title->Get());

        $fast_url =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fast_url->SetID('fast_url');
        $fast_url->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_fast_url', $this::t('FAST_URL'));
        $tpl->SetVariable('fast_url', $fast_url->Get());

        $selected_content = isset($selected_content)? $selected_content : '';
        $contents =& $this->app->loadEditor('Glossary', 'term_contents', $selected_content);
        $contents->setID('term_contents');
        $contents->TextArea->SetStyle('width: 99%; height: 260px;');
        $tpl->SetVariable('contents', $this::t('DESC'));
        $tpl->SetVariable('contents_field', $contents->Get());
        $dispTitle =& Piwi::CreateWidget('CheckButtons', 'display_title');

        $preview =& Piwi::CreateWidget('Button', 'previewButton', Jaws::t('PREVIEW'), STOCK_SAVE);
        $preview->SetID('previewButton');
        $preview->AddEvent(ON_CLICK, 'preview();');
        $tpl->SetVariable('preview_button', $preview->Get());

        $save =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $save->SetID('saveButton');
        $save->AddEvent(ON_CLICK, 'updateTerm();');
        $tpl->SetVariable('save', $save->Get());

        $del =& Piwi::CreateWidget('Button', 'delete', Jaws::t('DELETE'), STOCK_DELETE);
        $del->AddEvent(ON_CLICK, 'if (confirm(\'' . $this::t('CONFIRM_DELETE_TERM') . '\')) { deleteTerm(); }');
        $del->SetID('delButton');
        $tpl->SetVariable('delete', $del->Get());

        $cancel =& Piwi::CreateWidget('Button', 'cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'returnToEdit();');
        $cancel->SetID('cancelButton');
        $tpl->SetVariable('cancel', $cancel->Get());

        $edit =& Piwi::CreateWidget('Button', 'editButton', Jaws::t('EDIT'), STOCK_EDIT);
        $edit->AddEvent(ON_CLICK, 'switchTab(\'edit\')');
        $edit->SetID('editButton');
        $tpl->SetVariable('edit_button', $edit->Get());

        // Messages
        $this->gadget->export('incompleteGlossaryFields', Jaws::t('ERROR_INCOMPLETE_FIELDS'));
        $this->gadget->export('retrieving_message',       $this::t('MSGRETRIEVING'));
        $this->gadget->export('updating_message',         $this::t('MSGUPDATING'));
        $this->gadget->export('deleting_message',         $this::t('MSGDELETING'));
        $this->gadget->export('saving_message',           $this::t('MSGSAVING'));
        $this->gadget->export('sending_message',          $this::t('MSGSENDING'));

        // Acl
        $this->gadget->export('aclAddTerm', $this->gadget->GetPermission('AddTerm') ? 'true' : 'false');
        $this->gadget->export('aclEditTerm', $this->gadget->GetPermission('EditTerm') ? 'true' : 'false');
        $this->gadget->export('aclDeleteTerm', $this->gadget->GetPermission('DeleteTerm') ? 'true' : 'false');

        $tpl->ParseBlock('Glossary');
        return $tpl->Get();
    }
}