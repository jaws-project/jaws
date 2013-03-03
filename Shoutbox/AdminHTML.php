<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetAdmin
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Returns the default administration action to use if none is specified.
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        return $this->ManageComments();
    }

    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML of a datagrid
     */
    function CommentsDatagrid()
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHtml->Get($this->gadget->name);
    }

    /**
     * Builds the data (an array) of filtered comments
     *
     * @access  public
     * @param   int     $limit   Limit of comments
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  array   Filtered Comments
     */
    function CommentsData($limit = 0, $filter = '', $search = '', $status = '')
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHtml->GetDataAsArray(
            $this->gadget->name,
            BASE_SCRIPT . '?gadget=Shoutbox&amp;action=EditEntry&amp;id={id}',
            '',
            $filter,
            $search,
            $status,
            $limit
        );
    }

    /**
     * Displays shoutbox admin (comments manager)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Shoutbox/templates/');
        $tpl->Load('Admin.html');
        $tpl->SetBlock('shoutbox_admin');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('comments_where', _t('SHOUTBOX_COMMENTS_WHERE'));
        $tpl->SetVariable('status_label',   _t('GLOBAL_STATUS'));
        $tpl->SetVariable('deleteConfirm',  _t('SHOUTBOX_CONFIRM_MASIVE_DELETE_ENTRIES'));

        //Status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 1);
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 2);
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 3);
        $status->SetDefault('various');
        $status->AddEvent(ON_CHANGE, 'return searchComment();');
        $tpl->SetVariable('status', $status->Get());

        // filter by
        $filterByData = '';
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('SHOUTBOX_ID'), 'id');
        $filterBy->AddOption(_t('SHOUTBOX_ENTRY_CONTAINS'), 'comment');
        $filterBy->AddOption(_t('SHOUTBOX_NAME_CONTAINS'), 'name');
        $filterBy->AddOption(_t('SHOUTBOX_EMAIL_CONTAINS'), 'email');
        $filterBy->AddOption(_t('SHOUTBOX_URL_CONTAINS'), 'url');
        $filterBy->AddOption(_t('SHOUTBOX_IP_CONTAINS'), 'ip');
        $filterBy->SetDefault($filterByData);
        $tpl->SetVariable('filter_by', $filterBy->Get());

        // filter
        $filterData = '';
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('SHOUTBOX_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        // Display the data
        $tpl->SetVariable('comments', $this->CommentsDatagrid($filterByData, $filterData));

        ///Config properties
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Shoutbox'));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $fieldset = new Jaws_Widgets_FieldSet(_t('SHOUTBOX_SETTINGS'));
            $fieldset->SetDirection('vertical');

            //
            $limitcombo =& Piwi::CreateWidget('Combo', 'limit_entries');
            $limitcombo->SetTitle(_t('SHOUTBOX_ENTRY_LIMIT'));
            for ($i = 1; $i <= 20; ++$i) {
                $limitcombo->AddOption($i, $i);
            }
            $limit = $this->gadget->GetRegistry('limit');
            if (Jaws_Error::IsError($limit)) {
                $limit = 10;
            }
            $limitcombo->SetDefault($limit);
            $fieldset->Add($limitcombo);

            // max length
            $max_lencombo =& Piwi::CreateWidget('Combo', 'max_strlen');
            $max_lencombo->SetTitle(_t('SHOUTBOX_ENTRY_MAX_LEN'));
            for ($i = 1; $i <= 10; ++$i) {
                $max_lencombo->AddOption($i*25, $i*25);
            }
            $max_strlen = $this->gadget->GetRegistry('max_strlen');
            if (Jaws_Error::IsError($max_strlen)) {
                $max_strlen = 125;
            }
            $max_lencombo->SetDefault($max_strlen);
            $fieldset->Add($max_lencombo);

            //Anonymous post authority
            $authority =& Piwi::CreateWidget('Combo', 'authority');
            $authority->SetTitle(_t('SHOUTBOX_ANON_POST_AUTHORITY'));
            $authority->AddOption(_t('GLOBAL_DISABLED'), 'false');
            $authority->AddOption(_t('GLOBAL_ENABLED'),  'true');
            $anon_authority = $this->gadget->GetRegistry('anon_post_authority');
            $authority->SetDefault($anon_authority == 'true'? 'true' : 'false');
            $fieldset->Add($authority);

            $form->Add($fieldset);
            $submit =& Piwi::CreateWidget('Button', 'saveproperties', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $submit->AddEvent(ON_CLICK, 'javascript: updateProperties(this.form);');

            $form->Add($submit);
            $tpl->SetVariable('config_form', $form->Get());
        }


        $tpl->ParseBlock('shoutbox_admin');

        return $tpl->Get();
    }

    /**
     * Displays phoo comment to be edited
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditEntry()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comment = $cModel->GetComment($id, $this->gadget->name);
        if (Jaws_Error::IsError($comment)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Shoutbox&action=ManageComments');
        }

        $tpl = new Jaws_Template('gadgets/Shoutbox/templates/');
        $tpl->Load('EditComment.html');
        $tpl->SetBlock('edit_comment');
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'id', $comment['id']));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Shoutbox'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveEditEntry'));

        $name =& Piwi::CreateWidget('Entry', 'name', $comment['name']);
        $name->SetTitle(_t('GLOBAL_NAME'));

        $email =& Piwi::CreateWidget('Entry', 'email', $comment['email']);
        $email->SetTitle(_t('GLOBAL_EMAIL'));
        $email->SetStyle('direction: ltr;');

        $url =& Piwi::CreateWidget('Entry', 'url', $comment['url']);
        $url->SetTitle(_t('GLOBAL_URL'));
        $url->SetStyle('direction: ltr;');

        $ip =& Piwi::CreateWidget('Entry', 'ip', $comment['ip']);
        $ip->SetTitle(_t('GLOBAL_IP'));
        $ip->SetStyle('direction: ltr;');
        $ip->SetEnabled(false);

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $comment =& Piwi::CreateWidget('TextArea', 'comments', $xss->defilter($comment['msg_txt']));
        $comment->SetRows(5);
        $comment->SetColumns(60);
        $comment->SetStyle('width: 400px;');
        $comment->SetTitle(_t('SHOUTBOX_ENTRY'));

        $cancelButton =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, 'history.go(-1);');

        $submitButton =& Piwi::CreateWidget('Button', 'send', _t('GLOBAL_UPDATE'), STOCK_SAVE);
        $submitButton->SetSubmit();

        $deleteButton =& Piwi::CreateWidget('Button', 'delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $deleteButton->AddEvent(ON_CLICK, "this.form.action.value = 'DeleteComment'; this.form.submit();");

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle('float: right;');
        $buttonbox->PackStart($deleteButton);
        $buttonbox->PackStart($cancelButton);
        $buttonbox->PackStart($submitButton);

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('GLOBAL_UPDATE'));

        $fieldset->Add($name);
        $fieldset->Add($email);
        $fieldset->Add($url);
        $fieldset->Add($ip);
        $fieldset->Add($comment);
        $form->add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('edit_comment');
        return $tpl->Get();
    }

    /**
     * Save changes to entry.
     *
     * @access  public
     */
    function SaveEditEntry()
    {
        $model = $GLOBALS['app']->LoadGadget('Shoutbox', 'AdminModel');
        $req =& Jaws_Request::getInstance();

        $res = $model->UpdateComment($req->get('id', 'post'),
                              $req->get('name', 'post'),
                              $req->get('url', 'post'),
                              $req->get('email', 'post'),
                              $req->get('comments', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Shoutbox');
    }

    /**
     * Deletes a phoo comment
     *
     * @access  public
     */
    function DeleteComment()
    {
        $req =& Jaws_Request::getInstance();

        $model = $GLOBALS['app']->LoadGadget('Shoutbox', 'AdminModel');
        $model->DeleteComment($req->get('id', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Shoutbox');
    }
}
