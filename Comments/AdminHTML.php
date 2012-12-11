<?php
/**
 * Comments Core Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2009 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class CommentsAdminHTML extends Jaws_Gadget_HTML
{
    /*
     * Admin of Gadget
     *
     * @access  public
     * @return  string HTML content of administration
     */
    function Admin()
    {
        return '';
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $gadget   Gadget's name
     * @param   string  $action  Edit's action
     * @param   string  $filterby Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter   Filter data
     * @param   string  $status   Spam status (approved, waiting, spam)
     * @param   mixed   $limit    Data limit (numeric/boolean)
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($gadget, $editAction, $filterby, $filter, $status, $limit)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');

        $filterMode = '';
        switch($filterby) {
        case 'postid':
            $filterMode = COMMENT_FILTERBY_REFERENCE;
            break;
        case 'name':
            $filterMode = COMMENT_FILTERBY_NAME;
            break;
        case 'email':
            $filterMode = COMMENT_FILTERBY_EMAIL;
            break;
        case 'url':
            $filterMode = COMMENT_FILTERBY_URL;
            break;
        case 'title':
            $filterMode = COMMENT_FILTERBY_TITLE;
            break;
        case 'ip':
            $filterMode = COMMENT_FILTERBY_IP;
            break;
        case 'comment':
            $filterMode = COMMENT_FILTERBY_MESSAGE;
            break;
        case 'various':
            $filterMode = COMMENT_FILTERBY_VARIOUS;
            break;
        case 'status':
            $filterMode = COMMENT_FILTERBY_STATUS;
            break;
        default:
            $filterMode = null;
            break;
        }

        $comments = $cModel->GetFilteredComments($gadget, $filterMode, $filter, $status, $limit);
        if (Jaws_Error::IsError($comments)) {
            return array();
        }

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $date = $GLOBALS['app']->loadDate();
        $data = array();
        foreach ($comments as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            $newRow['name']    = $xss->filter($row['name']);
            $row['title'] = preg_replace("/(\r\n|\r)/", " ", $row['title']);
            if (!empty($editAction)) {
                $url = str_replace('{id}', $row['id'], $editAction);
                $newRow['title']   = '<a href="'.$url.'">'.$xss->filter($row['title']).'</a>';
            } else {
                $newRow['title']   = $row['title'];
            }
            $newRow['created'] = $date->Format($row['createtime']);
            $newRow['status']  = $row['status'];

            $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'), $url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

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
     * @param   string  $gadget   Gadget's name
     * @return  string  UI's XHTML
     */
    function Get($gadget)
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
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_NAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
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
        $actions->AddOption(_t('GLOBAL_MARK_AS_APPROVED'), 'approved');
        $actions->AddOption(_t('GLOBAL_MARK_AS_WAITING'), 'waiting');
        $actions->AddOption(_t('GLOBAL_MARK_AS_SPAM'), 'spam');

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