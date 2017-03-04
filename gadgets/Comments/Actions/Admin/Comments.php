<?php
/**
 * Comments Core Gadget
 *
 * @category    Gadget
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Comments extends Comments_Actions_Admin_Default
{
    /**
     * Show comments list
     *
     * @access  public
     * @param   string $req_gadget  Gadget name
     * @param   string $menubar     Menubar
     * @return  string XHTML template content
     */
    function Comments($req_gadget = '', $menubar = '')
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmCommentDelete', _t('COMMENTS_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->loadAdmin('Comments.html');
        $tpl->SetBlock('Comments');
        //Menu bar
        $tpl->SetVariable('menubar', empty($menubar)? $this->MenuBar('Comments') : $menubar);

        if (empty($req_gadget)) {
            //Gadgets filter label
            $lblGadget =& Piwi::CreateWidget('Label', _t('COMMENTS_GADGETS').': ', 'gadgets_filter');
            $tpl->SetVariable('lbl_gadgets_filter', $lblGadget->Get());

            //Gadgets filter
            $filterGadgets =& Piwi::CreateWidget('Combo', 'gadgets_filter');
            $filterGadgets->SetID('gadgets_filter');
            $filterGadgets->setStyle('width: 100px;');
            $filterGadgets->AddEvent(ON_CHANGE, "searchComment()");
            $filterGadgets->AddOption(_t('GLOBAL_ALL'), '');
            $filterGadgets->AddOption(_t('COMMENTS_TITLE'), 'Comments');
            $gadgets = $this->gadget->model->load()->recommendedfor();
            if (!Jaws_Error::IsError($gadgets)) {
                foreach ($gadgets as $gadget) {
                    $filterGadgets->AddOption(_t(strtoupper($gadget.'_TITLE')), $gadget);
                }
            }
            $filterGadgets->SetDefault('');
        } else {
            $filterGadgets =& Piwi::CreateWidget('HiddenEntry', 'gadgets_filter', $req_gadget);
            $filterGadgets->SetID('gadgets_filter');
        }
        $tpl->SetVariable('gadgets_filter', $filterGadgets->Get());

        //Status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption(_t('GLOBAL_ALL'), 0);
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $status->AddOption(_t('COMMENTS_STATUS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $status->AddOption(_t('COMMENTS_STATUS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);
        $status->SetDefault(0);
        $status->AddEvent(ON_CHANGE, 'searchComment();');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $status->Get());

        // filter
        $filterData = jaws()->request->fetch('filter', 'get');
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', is_null($filterData)? '' : $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('COMMENTS_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        // DataGrid
        $tpl->SetVariable('datagrid', $this->getDataGrid($req_gadget));
        // CommentUI
        $tpl->SetVariable('comment_ui', $this->CommentUI());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'comments_actions');
        $actions->SetID('comments_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('COMMENTS_MARK_AS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $actions->AddOption(_t('COMMENTS_MARK_AS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $actions->AddOption(_t('COMMENTS_MARK_AS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $actions->AddOption(_t('COMMENTS_MARK_AS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeCommentAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:commentDGAction($('#comments_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());

        if ($this->gadget->GetPermission('ManageComments')) {
            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'stopCommentAction();');
            $btnCancel->SetStyle('display: none;');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "updateComment(false);");
            $btnSave->SetStyle('display: none;');
            $tpl->SetVariable('btn_save', $btnSave->Get());

            $btnReply =& Piwi::CreateWidget('Button', 'btn_reply', _t('COMMENTS_SAVE_AND_REPLY'), 
                                                        'gadgets/Contact/Resources/images/contact_mini.png');
            $btnReply->AddEvent(ON_CLICK, "updateComment(true);");
            $btnReply->SetStyle('display: none;');
            $tpl->SetVariable('btn_reply', $btnReply->Get());
        }

        $tpl->SetVariable('legend_title', _t('COMMENTS_EDIT_MESSAGE_DETAILS'));

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
        $tpl = $this->gadget->template->loadAdmin('Comments.html');
        $tpl->SetBlock('CommentUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));

        //Date
        $tpl->SetVariable('lbl_date', _t('GLOBAL_DATE'));

        //Reference Date
        $tpl->SetVariable('lbl_reference_url', _t('COMMENTS_REFERENCE_URL'));

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
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), Comments_Info::COMMENTS_STATUS_APPROVED);
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), Comments_Info::COMMENTS_STATUS_WAITING);
        $status->AddOption(_t('COMMENTS_STATUS_SPAM'), Comments_Info::COMMENTS_STATUS_SPAM);
        $status->AddOption(_t('COMMENTS_STATUS_PRIVATE'), Comments_Info::COMMENTS_STATUS_PRIVATE);
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
        if(!$this->gadget->GetPermission('ReplyComments')) {
            $replyText->SetEnabled(false);
        }
        $tpl->SetVariable('lbl_reply', _t('COMMENTS_REPLY'));
        $tpl->SetVariable('reply', $replyText->Get());

        $tpl->ParseBlock('CommentUI');
        return $tpl->Get();
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $requester  Requester gadget name
     * @param   string  $gadget     Gadget name
     * @param   string  $term       Search term
     * @param   int     $status     Spam status (approved=1, waiting=2, spam=3)
     * @param   mixed   $offset     Data offset (numeric/boolean)
     * @param   int     $orderBy    Data order
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($requester, $gadget, $term, $status, $offset, $orderBy)
    {
        $data = array();
        $cModel = $this->gadget->model->load('Comments');
        $comments = $cModel->GetComments($gadget, '', '', $term, $status, 15, $offset, $orderBy);
        if (Jaws_Error::IsError($comments)) {
            return $data;
        }

        $date = Jaws_Date::getInstance();
        $data = array();
        foreach ($comments as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            if ($requester == $this->gadget->name) {
                $newRow['gadget'] = _t(strtoupper($row['gadget']).'_TITLE');
            }
            $comment = Jaws_UTF8::strlen($row['msg_txt']) > 25 ?
                (Jaws_UTF8::substr($row['msg_txt'], 0, 22). '...') : $row['msg_txt'];
            $comment = "<abbr title='" . $row['msg_txt'] . "'>$comment</abbr>";
            $link =& Piwi::CreateWidget('Link', $comment, "javascript:editComment(this, '{$row['id']}');");
            $newRow['comment'] = $link->Get();
            $newRow['name'] = $row['name'];
            $newRow['created'] = $date->Format($row['insert_time']);
            if ($row['status'] == Comments_Info::COMMENTS_STATUS_APPROVED) {
                $status = _t('COMMENTS_STATUS_APPROVED');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_WAITING) {
                $status = _t('COMMENTS_STATUS_WAITING');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_SPAM) {
                $status = _t('COMMENTS_STATUS_SPAM');
            } elseif ($row['status'] == Comments_Info::COMMENTS_STATUS_PRIVATE) {
                $status = _t('COMMENTS_STATUS_PRIVATE');
            }
            $newRow['status']  = $status;
            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Builds and returns the UI
     *
     * @access  public
     * @param   string $gadget  Caller gadget name
     * @return  string  UI XHTML
     */
    function getDataGrid($gadget)
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('comments_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->useMultipleSelection();
        if (empty($gadget)) {
            $grid->AddColumn(Piwi::CreateWidget('Column', _t('COMMENTS_GADGETS')), null, false);
        }
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('COMMENTS_COMMENT')), null, false);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME')), null, false);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_CREATED')), null, false);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_STATUS')), null, false);
        return $grid->Get();
    }

}