<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
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
        $tpl->SetVariable('legend_title', _t('CONTACT_RECIPIENTS_ADD'));

        $titleentry =& Piwi::CreateWidget('Entry', 'name', '');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('name', $titleentry->Get());

        $emailentry =& Piwi::CreateWidget('Entry', 'email', '');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $emailentry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'tel', '');
        $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
        $tpl->SetVariable('tel', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fax', '');
        $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
        $tpl->SetVariable('fax', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'mobile', '');
        $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
        $tpl->SetVariable('mobile', $entry->Get());

        $informType =& Piwi::CreateWidget('Combo', 'inform_type');
        $informType->SetID('inform_type');
        $informType->AddOption(_t('GLOBAL_DISABLE'), 0);
        $informType->AddOption(_t('GLOBAL_EMAIL'),   1);
        $informType->SetDefault(0);
        $tpl->SetVariable('lbl_inform_type', _t('CONTACT_RECIPIENTS_INFORM_TYPE'));
        $tpl->SetVariable('inform_type', $informType->Get());

        $visibleType =& Piwi::CreateWidget('Combo', 'visible');
        $visibleType->SetID('visible');
        $visibleType->AddOption(_t('GLOBAL_NO'),  0);
        $visibleType->AddOption(_t('GLOBAL_YES'), 1);
        $visibleType->SetDefault(1);
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE'));
        $tpl->SetVariable('visible', $visibleType->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled($this->gadget->GetPermission('ManageRecipients'));
        $btnSave->AddEvent(ON_CLICK, 'updateRecipient();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('incompleteRecipientFields', _t('CONTACT_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmRecipientDelete',    _t('CONTACT_CONFIRM_DELETE_RECIPIENT'));

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
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_EMAIL'), null, false);
        $column2->SetStyle('width:160px; white-space:nowrap;');
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_VISIBLE'), null, false);
        $column3->SetStyle('width:56px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
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
            $recipientData['visible'] = ($recipient['visible']?_t('GLOBAL_YES') : _t('GLOBAL_NO'));
            $actions = '';
            if ($this->gadget->GetPermission('ManageRecipients')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editRecipient(this, '".$recipient['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteRecipient(this, '".$recipient['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $recipientData['actions'] = $actions;
            $newData[] = $recipientData;
        }
        return $newData;
    }

}