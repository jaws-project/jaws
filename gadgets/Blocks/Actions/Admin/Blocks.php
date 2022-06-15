<?php
/**
 * Blocks Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Actions_Admin_Blocks extends Jaws_Gadget_Action
{
    /**
     * Prepares the block management view
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Blocks()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Blocks.html');
        $tpl->SetBlock('blocks');

        $tpl->SetVariable('base_script', BASE_SCRIPT);

        // Block List
        $blocksCombo =& Piwi::CreateWidget('Combo', 'block_id');
        $blocksCombo->SetID('block_id');
        $blocksCombo->SetSize(16);
        $blocksCombo->AddEvent(ON_CHANGE, 'edit(this.value, \'' . Jaws::t('EDIT') . '\');');
        $model = $this->gadget->model->load('Block');
        $blocks = $model->GetBlocks(false);
        if (!Jaws_Error::isError($blocks)) {
            foreach ($blocks as $b) {
                $blocksCombo->AddOption($b['title'], $b['id']);
            }
            $blocksCombo->SetDefault(0);
        }
        $tpl->SetVariable('block_list', $blocksCombo->Get());

        // New Button
        if ($this->gadget->GetPermission('AddBlock')) {
            $newButton =& Piwi::CreateWidget('Button', 'newButton', $this::t('NEW'), STOCK_NEW);
            $newButton->AddEvent(ON_CLICK, 'createNewBlock(\'' . $this::t('NEW') . '\');');
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
        $title =& Piwi::CreateWidget('Entry', 'title', '', Jaws::t('TITLE'));
        $title->SetID('block_title');
        $title->SetStyle('width: 99%');
        $tpl->SetVariable('lbl_block_id', Jaws::t('ID'));
        $tpl->SetVariable('block_title',  Jaws::t('TITLE'));
        $tpl->SetVariable('title_field', $title->Get());

        // block summary
        $summary =& $this->app->loadEditor('Blocks', 'block_summary');
        $summary->setID('block_summary');
        $summary->TextArea->SetStyle('width: 99%;');
        $tpl->SetVariable('summary', $this::t('SUMMARY'));
        $tpl->SetVariable('summary_field', $summary->Get());

        // block content
        $content =& $this->app->loadEditor('Blocks', 'block_content');
        $content->setID('block_content');
        $content->TextArea->SetStyle('width: 99%;');
        $tpl->SetVariable('content', $this::t('CONTENT'));
        $tpl->SetVariable('content_field', $content->Get());

        $dispTitle =& Piwi::CreateWidget('CheckButtons', 'display_title');
        // FIXME: This is an ugly hack to add an ID to a Option...
        $dispTitle->AddOption($this::t('DISPLAYTITLE'), 'true', null, true);
        $tpl->SetVariable('display_title', $dispTitle->Get());

        $preview =& Piwi::CreateWidget('Button', 'previewButton', Jaws::t('PREVIEW'), STOCK_NEW);
        $preview->SetID('previewButton');
        $preview->AddEvent(ON_CLICK, 'preview();');
        $tpl->SetVariable('preview_button', $preview->Get());

        $save =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $save->SetID('saveButton');
        $save->AddEvent(ON_CLICK, 'updateBlock();');
        $tpl->SetVariable('save', $save->Get());

        if ($this->gadget->GetPermission('DeleteBlock')) {
            $del =& Piwi::CreateWidget('Button', 'delete', Jaws::t('DELETE'), STOCK_DELETE);
            $del->AddEvent(ON_CLICK, 'if (confirm(\'' . $this::t('CONFIRM_DELETE_BLOCK') . '\')) { deleteBlock(); }');
            $del->SetID('delButton');
            $tpl->SetVariable('delete', $del->Get());
        } else {
            $tpl->SetVariable('delete', '');
        }

        $cancel =& Piwi::CreateWidget('Button', 'cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'returnToEdit();');
        $cancel->SetID('cancelButton');
        $tpl->SetVariable('cancel', $cancel->Get());

        $edit =& Piwi::CreateWidget('Button', 'editButton', Jaws::t('EDIT'), STOCK_EDIT);
        $edit->AddEvent(ON_CLICK, 'switchTab(\'edit\')');
        $edit->SetID('editButton');
        $tpl->SetVariable('edit_button', $edit->Get());

        // Messages
        $this->gadget->define('incompleteBlockFields', Jaws::t('ERROR_INCOMPLETE_FIELDS'));
        $this->gadget->define('retrievingMessage',    $this::t('MSGRETRIEVING'));
        $this->gadget->define('updatingMessage',      $this::t('MSGUPDATING'));
        $this->gadget->define('deletingMessage',      $this::t('MSGDELETING'));
        $this->gadget->define('savingMessage',        $this::t('MSGSAVING'));
        $this->gadget->define('sendingMessage',       $this::t('MSGSENDING'));

        // Acl
        $this->gadget->define('aclAddBlock', $this->gadget->GetPermission('AddBlock')?'true':'false');
        $this->gadget->define('aclEditBlock', $this->gadget->GetPermission('EditBlock')?'true':'false');
        $this->gadget->define('aclDeleteBlock', $this->gadget->GetPermission('DeleteBlock')?'true':'false');

        $tpl->ParseBlock('blocks');
        return $tpl->Get();
    }

}