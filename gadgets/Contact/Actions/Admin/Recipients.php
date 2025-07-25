<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Admin_Recipients extends Contact_Actions_Admin_Default
{
    /**
     * Builds recipients UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Recipients()
    {
        $this->gadget->CheckPermission('ManageRecipients');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Recipients.html');
        $tpl->SetBlock('recipients');

        $tpl->SetVariable('menubar', $this->MenuBar('Recipients'));
        $tpl->SetVariable('grid', $this->RecipientsDataGrid());

        // Tabs titles
        $tpl->SetVariable('legend_title', $this::t('RECIPIENTS_ADD'));

        $titleentry =& Piwi::CreateWidget('Entry', 'name', '');
        $tpl->SetVariable('lbl_name', Jaws::t('TITLE'));
        $tpl->SetVariable('name', $titleentry->Get());

        $emailentry =& Piwi::CreateWidget('Entry', 'email', '');
        $tpl->SetVariable('lbl_email', Jaws::t('EMAIL'));
        $tpl->SetVariable('email', $emailentry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'tel', '');
        $tpl->SetVariable('lbl_tel', $this::t('TEL'));
        $tpl->SetVariable('tel', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fax', '');
        $tpl->SetVariable('lbl_fax', $this::t('FAX'));
        $tpl->SetVariable('fax', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'mobile', '');
        $tpl->SetVariable('lbl_mobile', $this::t('MOBILE'));
        $tpl->SetVariable('mobile', $entry->Get());

        $informtypes = array_map('basename', glob(ROOT_JAWS_PATH . 'gadgets/Contact/Informs/*.php'));
        $informType =& Piwi::CreateWidget('Combo', 'inform_type');
        $informType->SetID('inform_type');
        $informType->AddOption(Jaws::t('DISABLE'), 0);
        foreach ($informtypes as $inform) {
            $inform = basename($inform, '.php');
            $informType->AddOption($inform, $inform);
        }
        $informType->SetDefault(0);
        $tpl->SetVariable('lbl_inform_type', $this::t('RECIPIENTS_INFORM_TYPE'));
        $tpl->SetVariable('inform_type', $informType->Get());

        $visibleType =& Piwi::CreateWidget('Combo', 'visible');
        $visibleType->SetID('visible');
        $visibleType->AddOption(Jaws::t('NOO'),  0);
        $visibleType->AddOption(Jaws::t('YESS'), 1);
        $visibleType->SetDefault(1);
        $tpl->SetVariable('lbl_visible', Jaws::t('VISIBLE'));
        $tpl->SetVariable('visible', $visibleType->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled($this->gadget->GetPermission('ManageRecipients'));
        $btnSave->AddEvent(ON_CLICK, 'updateRecipient();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $this->gadget->export('incompleteRecipientFields', $this::t('INCOMPLETE_FIELDS'));
        $this->gadget->export('confirmRecipientDelete',    $this::t('CONFIRM_DELETE_RECIPIENT'));

        $tpl->ParseBlock('recipients');

        return $tpl->Get();
    }

    /**
     * Prepares the datagrid view (XHTML of datagrid)
     *
     * @access  public
     * @return  string XHTML template of datagrid
     */
    function RecipientsDataGrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('contacts_recipients');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->SetID('recipient_datagrid');
        $column1 = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', Jaws::t('EMAIL'), null, false);
        $column2->SetStyle('width:160px; white-space:nowrap;');
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', Jaws::t('VISIBLE'), null, false);
        $column3->SetStyle('width:56px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column4->SetStyle('width:60px; white-space:nowrap;');
        $datagrid->AddColumn($column4);


        return $datagrid->Get();
    }

    /**
     * Prepares the data of recipient
     *
     * @access  public
     * @param   int    $offset  offset of data
     * @return  array  Data array
     */
    function GetRecipients($offset = null)
    {
        $model = $this->gadget->model->load('Recipients');
        $recipients = $model->GetRecipients(false, 10, $offset);
        if (Jaws_Error::IsError($recipients)) {
            return array();
        }

        $newData = array();
        foreach ($recipients as $recipient) {
            $recipientData = array();
            $recipientData['name']  = $recipient['name'];
            $recipientData['email'] = $recipient['email'];
            $recipientData['visible'] = ($recipient['visible']?Jaws::t('YESS') : Jaws::t('NOO'));
            $actions = '';
            if ($this->gadget->GetPermission('ManageRecipients')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                                            "javascript:editRecipient(this, '".$recipient['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                                            "javascript:deleteRecipient(this, '".$recipient['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $recipientData['actions'] = $actions;
            $newData[] = $recipientData;
        }
        return $newData;
    }

}