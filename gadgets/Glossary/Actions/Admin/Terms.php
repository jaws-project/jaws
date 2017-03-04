<?php
/**
 * Glossary Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
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
        $termsCombo->AddEvent(ON_CHANGE, 'edit(this.value, \'' . _t('GLOBAL_EDIT') . '\');');
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
            $newButton =& Piwi::CreateWidget('Button', 'newButton', _t('GLOBAL_CREATE', _t('GLOSSARY_TERM')), STOCK_NEW);
            $newButton->AddEvent(ON_CLICK, 'createNewTerm(\'' . _t('GLOBAL_CREATE', _t('GLOSSARY_TERM')) . '\');');
            $newButton->SetID('newButton');
            $tpl->SetVariable('new_button', $newButton->Get());
        } else {
            $tpl->SetVariable('new_button', '');
        }

        // Tabs titles
        $tpl->SetVariable('edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('preview', _t('GLOBAL_PREVIEW'));
        // Edit form
        $idHidden =& Piwi::CreateWidget('HiddenEntry', 'id');
        $idHidden->SetID('hidden_id');
        $tpl->SetVariable('hidden_id', $idHidden->Get());

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetID('term_title');
        $title->SetStyle('width: 256px;');
        $tpl->SetVariable('term_title', _t('GLOSSARY_TERM'));
        $tpl->SetVariable('title_field', $title->Get());

        $fast_url =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fast_url->SetID('fast_url');
        $fast_url->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_fast_url', _t('GLOSSARY_FAST_URL'));
        $tpl->SetVariable('fast_url', $fast_url->Get());

        $selected_content = isset($selected_content)? $selected_content : '';
        $contents =& $GLOBALS['app']->LoadEditor('Glossary', 'term_contents', $selected_content);
        $contents->setID('term_contents');
        $contents->TextArea->SetStyle('width: 99%; height: 260px;');
        $tpl->SetVariable('contents', _t('GLOSSARY_DESC'));
        $tpl->SetVariable('contents_field', $contents->Get());
        $dispTitle =& Piwi::CreateWidget('CheckButtons', 'display_title');

        $preview =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_PREVIEW'), STOCK_SAVE);
        $preview->SetID('previewButton');
        $preview->AddEvent(ON_CLICK, 'preview();');
        $tpl->SetVariable('preview_button', $preview->Get());

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->SetID('saveButton');
        $save->AddEvent(ON_CLICK, 'updateTerm();');
        $tpl->SetVariable('save', $save->Get());

        $del =& Piwi::CreateWidget('Button', 'delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $del->AddEvent(ON_CLICK, 'if (confirm(\'' . _t('GLOSSARY_CONFIRM_DELETE_TERM') . '\')) { deleteTerm(); }');
        $del->SetID('delButton');
        $tpl->SetVariable('delete', $del->Get());

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'returnToEdit();');
        $cancel->SetID('cancelButton');
        $tpl->SetVariable('cancel', $cancel->Get());

        $edit =& Piwi::CreateWidget('Button', 'editButton', _t('GLOBAL_EDIT'), STOCK_EDIT);
        $edit->AddEvent(ON_CLICK, 'switchTab(\'edit\')');
        $edit->SetID('editButton');
        $tpl->SetVariable('edit_button', $edit->Get());

        // Messages
        $this->gadget->define('incompleteGlossaryFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $this->gadget->define('retrieving_message',       _t('GLOSSARY_MSGRETRIEVING'));
        $this->gadget->define('updating_message',         _t('GLOSSARY_MSGUPDATING'));
        $this->gadget->define('deleting_message',         _t('GLOSSARY_MSGDELETING'));
        $this->gadget->define('saving_message',           _t('GLOSSARY_MSGSAVING'));
        $this->gadget->define('sending_message',          _t('GLOSSARY_MSGSENDING'));

        // Acl
        $this->gadget->define('aclAddTerm', $this->gadget->GetPermission('AddTerm') ? 'true' : 'false');
        $this->gadget->define('aclEditTerm', $this->gadget->GetPermission('EditTerm') ? 'true' : 'false');
        $this->gadget->define('aclDeleteTerm', $this->gadget->GetPermission('DeleteTerm') ? 'true' : 'false');

        $tpl->ParseBlock('Glossary');
        return $tpl->Get();
    }
}