<?php
/**
 * Builds the UI for comments (the datagrid with its sexy forms)
 *
 * @category   Widget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Widgets_CommentUI
{
    /**
     * Gadget's name
     *
     * @access  private
     * @var     string
     */
    var $_gadget;

    /**
     * Edit's action
     *
     * @access  private
     * @var     string
     */
    var $_editAction = '';

    /**
     * Public constructor that sets the gadget's name
     *
     * @access  public
     * @param   string   $gadget    Gadget's name
     * @return  void
     **/
    function Jaws_Widgets_CommentUI($gadget)
    {
        $this->_gadget = $gadget;
    }

    /**
     * Sets the edit action
     *
     * @access  public
     * @param   string  $action  Edit's action
     * @return  void
     */
    function SetEditAction($action)
    {
        $this->_editAction = $action;
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $filterby   Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter     Filter data
     * @param   string  $status     Spam status (approved, waiting, spam)
     * @param   mixed   $limit      Data limit (numeric/boolean)
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($filterby, $filter, $status, $limit)
    {
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_gadget);

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

        $comments = $api->GetFilteredComments($filterMode, $filter, $status, $limit);
        if (Jaws_Error::IsError($comments)) {
            return array();
        }

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $date = $GLOBALS['app']->loadDate();
        $data = array();
        foreach ($comments as $row) {
            $newRow = array();
            $newRow['__KEY__'] = $row['id'];
            $newRow['name']    = $row['name'];
            if (empty($row['title'])) {
                $row['title'] = Jaws_UTF8::substr(strip_tags($xss->defilter($row['msg_txt'])),0, 50);
            }

            $row['title'] = preg_replace("/(\r\n|\r)/", " ", $row['title']);
            if (!empty($this->_editAction)) {
                $url = str_replace('{id}', $row['id'], $this->_editAction);
                $newRow['title'] = '<a href="'.$url.'">'.$row['title'].'</a>';
            } else {
                $newRow['title'] = $row['title'];
            }
            $newRow['created'] = $date->Format($row['createtime']);
            $newRow['status']  = _t('GLOBAL_STATUS_'. strtoupper($row['status']));

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
     * @return  string  UI's XHTML
     */
    function Get()
    {
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api   = new Jaws_Comment($this->_gadget);
        $total = $api->TotalOfComments('');

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