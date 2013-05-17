<?php
/**
 * Comments Core Gadget
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_AdminHTML extends Jaws_Gadget_HTML
{
    /*
     * Admin of Gadget
     *
     * @access  public
     * @return  string HTML content of administration
     */
    function Admin()
    {
        // TODO: Check Permission For Manage Comments
        return $this->Comments();
    }

    /**
     * Prepares the comments menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Comments', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Comments';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        // TODO: Check Permission For Manage Comments
        $menubar->AddOption(
            'Comments',
            _t('COMMENTS_NAME'),
            BASE_SCRIPT . '?gadget=Comments&amp;action=Admin');

        $menubar->AddOption(
            'Settings',
            _t('GLOBAL_SETTINGS'),
            BASE_SCRIPT . '?gadget=Comments&amp;action=Settings',
            STOCK_PREFERENCES);

        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Show comments list
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Comments()
    {
        // TODO: Check Permission For Manage Comments
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->loadTemplate('Comments.html');
        $tpl->SetBlock('Comments');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Comments'));

        //load other gadget translations
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        //Gadgets filter
        $gadgetsCombo =& Piwi::CreateWidget('Combo', 'gadgets_filter');
        $gadgetsCombo->SetID('gadgets_filter');
        $gadgetsCombo->setStyle('width: 100px;');
        $gadgetsCombo->AddEvent(ON_CHANGE, "searchComment()");
        $gadgetsCombo->AddOption('', '');
        // TODO: Get List Of Gadget Which Use Comments
        $gadgetsCombo->AddOption(_t('COMMENTS_NAME'), 'Comments');
        $gadgetsCombo->AddOption(_t('BLOG_NAME'), 'Blog');
        $gadgetsCombo->AddOption(_t('PHOO_NAME'), 'Phoo');
        $gadgetsCombo->AddOption(_t('SHOUTBOX_NAME'), 'Shoutbox');
        $gadgetsCombo->SetDefault('');
        $tpl->SetVariable('lbl_gadgets_filter', _t('COMMENTS_GADGETS'));
        $tpl->SetVariable('gadgets_filter', $gadgetsCombo->Get());

        //Status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 1);
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 2);
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 3);
        $status->SetDefault('various');
        $status->AddEvent(ON_CHANGE, 'searchComment();');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        // filter by
        $request =& Jaws_Request::getInstance();
        $filterByData = $request->get('filterby', 'get');
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_POST_ID_IS'), 'postid');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_COMMENT_CONTAINS'), 'comment');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_NAME_CONTAINS'), 'name');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_EMAIL_CONTAINS'), 'email');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_URL_CONTAINS'), 'url');
        $filterBy->AddOption(_t('COMMENTS_SEARCH_IP_IS'), 'ip');
        $filterBy->SetDefault(is_null($filterByData)? '' : $filterByData);
        $tpl->SetVariable('lbl_filter_by', _t('COMMENTS_FILTER_BY'));
        $tpl->SetVariable('filter_by', $filterBy->Get());

        // filter
        $filterData = $request->get('filter', 'get');
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', is_null($filterData)? '' : $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('COMMENTS_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        //DataGrid
        $tpl->SetVariable('grid', $this->Get('', true));

        //CommentUI
        $tpl->SetVariable('comment_ui', $this->CommentUI());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display: none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        // TODO: Check Permission For Manage Comments
        //$btnSave->SetEnabled($this->gadget->GetPermission('ManageComments'));
        $btnSave->AddEvent(ON_CLICK, "updateComment('update');");
        $btnSave->SetStyle('display: none;');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnReply =& Piwi::CreateWidget('Button', 'btn_reply', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnReply->AddEvent(ON_CLICK, "updateComment('reply');");
        $btnReply->SetStyle('display: none;');
        $tpl->SetVariable('btn_reply', $btnReply->Get());

        $tpl->SetVariable('incompleteCommentsFields', _t('COMMENTS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmCommentDelete',    _t('COMMENTS_CONFIRM_DELETE'));
        $tpl->SetVariable('legend_title',            _t('COMMENTS_EDIT_MESSAGE_DETAILS'));
        $tpl->SetVariable('messageDetail_title',     _t('COMMENTS_EDIT_MESSAGE_DETAILS'));

        $tpl->ParseBlock('Comments');
        return $tpl->Get();
    }

    /**
     * Show a form to show/edit a given comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CommentUI()
    {
        $tpl = $this->gadget->loadTemplate('Comments.html');
        $tpl->SetBlock('CommentUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $nameEntry->setStyle('width: 160px;');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //url
        $nameEntry =& Piwi::CreateWidget('Entry', 'url', '');
        $nameEntry->setStyle('width: 270px;');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $nameEntry->Get());

        //Status
        $status =& Piwi::CreateWidget('Combo', 'comment_status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 1);
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 2);
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 3);
        $status->SetDefault('various');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetStyle('width: 270px;');
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('COMMENTS_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());

        //reply
        $replyText =& Piwi::CreateWidget('TextArea', 'reply','');
        $replyText->SetStyle('width: 270px;');
        $replyText->SetRows(8);
        $tpl->SetVariable('lbl_reply', _t('COMMENTS_REPLY'));
        $tpl->SetVariable('reply', $replyText->Get());

        $tpl->ParseBlock('CommentUI');
        return $tpl->Get();
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $editAction Edit action
     * @param   string  $replyAction
     * @param   string  $term       Search term
     * @param   int     $status     Spam status (approved=1, waiting=2, spam=3)
     * @param   mixed   $limit      Data limit (numeric/boolean)
     * @param   bool    $gadgetColumn   Display gadget column?
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($gadget, $editAction, $replyAction, $term, $status, $limit, $gadgetColumn=false)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');
        $comments = $cModel->GetFilteredComments($gadget, '', $term, $status, $limit);
        if (Jaws_Error::IsError($comments)) {
            return array();
        }

        if ($gadgetColumn) {
            //load other gadget translations
            $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
            $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
            $GLOBALS['app']->Translate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
            $GLOBALS['app']->Translate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);
        }

        $date = $GLOBALS['app']->loadDate();
        $data = array();
        foreach ($comments as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];

            if($gadgetColumn) {
                $newRow['gadget']  = _t(strtoupper($row['gadget']).'_NAME');
            }

            $newRow['name']    = $row['name'];

            if (!empty($editAction)) {
                $edit_url = str_replace('{id}', $row['id'], $editAction);
            }

            $newRow['created'] = $date->Format($row['createtime']);
            if($row['status']==1) {
                $status_name = 'APPROVED';
            } else if($row['status']==2) {
                $status_name = 'WAITING';
            } else if ($row['status']==3) {
                $status_name = 'SPAM';
            }

            $newRow['status']  = _t('GLOBAL_STATUS_'. $status_name);

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'), $edit_url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

            if (!empty($replyAction)) {
                $reply_url = str_replace('{id}', $row['id'], $replyAction);

                $link =& Piwi::CreateWidget('Link', _t('COMMENTS_REPLY'), $reply_url,
                    'gadgets/Comments/images/reply-mini.png');
                $actions .= $link->Get() . '&nbsp;';
            }

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                        "javascript: commentDelete('".$row['id']."');",
                                        STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $newRow['actions'] = $actions;

            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Builds and returns the UI
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   bool    $gadgetColumn   Display gadget column?
     * @return  string  UI XHTML
     */
    function Get($gadget, $gadgetColumn=false)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $total  = $cModel->TotalOfComments($gadget, '');

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('comments_box');
        $gridBox->SetStyle('width: 100%;');

        //Datagrid
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('comments_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->TotalRows($total);
        $grid->useMultipleSelection();
        if($gadgetColumn) {
            $grid->AddColumn(Piwi::CreateWidget('Column', _t('COMMENTS_GADGETS')));
        }
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_CREATED')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('comments_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'comments_actions');
        $actions->SetID('comments_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('GLOBAL_MARK_AS_APPROVED'), 1);
        $actions->AddOption(_t('GLOBAL_MARK_AS_WAITING'), 2);
        $actions->AddOption(_t('GLOBAL_MARK_AS_SPAM'), 3);

        $execute =& Piwi::CreateWidget('Button', 'executeCommentAction', '',
                                       STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript: commentDGAction(document.getElementById('comments_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($grid);
        $gridBox->Add($gridForm);

        return $gridBox->Get();
    }

}