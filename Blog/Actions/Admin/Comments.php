<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Comments extends Blog_AdminHTML
{
    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML template of a datagrid
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
     * @param   int     $limit      Limit of comments
     * @param   string  $filter     Filter
     * @param   string  $search     Search word
     * @param   string  $status     Spam status (approved, waiting, spam)
     * @return  array   Filtered Comments
     */
    function CommentsData($limit = 0, $filter = '', $search = '', $status = '')
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHtml->GetDataAsArray(
            $this->gadget->name,
            BASE_SCRIPT . '?gadget=Blog&amp;action=EditComment&amp;id={id}',
            $filter,
            $search,
            $status,
            $limit
        );
    }

    /**
     * Displays blog comments manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        if (!Jaws_Gadget::IsGadgetInstalled('Comments')) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog');
        }

        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ManageComments.html');
        $tpl->SetBlock('manage_comments');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar('ManageComments'));

        $tpl->SetVariable('comments_where', _t('BLOG_COMMENTS_WHERE'));
        $tpl->SetVariable('status_label', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('deleteConfirm', _t('BLOG_DELETE_MASSIVE_COMMENTS'));

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
        $request =& Jaws_Request::getInstance();
        $filterByData = $request->get('filterby', 'get');
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('BLOG_POST_ID_IS'), 'postid');
        $filterBy->AddOption(_t('BLOG_COMMENT_CONTAINS'), 'comment');
        $filterBy->AddOption(_t('BLOG_NAME_CONTAINS'), 'name');
        $filterBy->AddOption(_t('BLOG_EMAIL_CONTAINS'), 'email');
        $filterBy->AddOption(_t('BLOG_URL_CONTAINS'), 'url');
        $filterBy->AddOption(_t('BLOG_IP_IS'), 'ip');
        $filterBy->SetDefault(is_null($filterByData)? '' : $filterByData);
        $tpl->SetVariable('filter_by', $filterBy->Get());

        // filter
        $filterData = $request->get('filter', 'get');
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', is_null($filterData)? '' : $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('BLOG_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        // Display the data
        $tpl->SetVariable('comments', $this->CommentsDatagrid($filterByData, $filterData));
        $tpl->ParseBlock('manage_comments');
        return $tpl->Get();
    }

    /**
     * Displays blog comment to be edited
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $request =& Jaws_Request::getInstance();

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        // Fetch the comment
        $comment = $model->GetComment($request->get('id', 'get'));
        if (Jaws_Error::IsError($comment)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
        }

        // Fetch the entry
        ///FIXME we need to either create a query for this or make getEntry only fetch the title, this is a overkill atm
        $entry = $model->getEntry($comment['reference']);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('EditComment.html');
        $tpl->SetBlock('edit_comment');
        $tpl->SetVariable('menubar', $this->MenuBar('ManageComments'));

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('BLOG_UPDATE_COMMENT'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'id', $comment['id']));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Blog'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveEditComment'));
        $permalink = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $comment['reference']));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'permalink', $permalink));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'status', $comment['status']));

        $text = '<strong>' . $entry['title'] . '</strong>';
        $staticText =& Piwi::CreateWidget('StaticEntry', _t('BLOG_COMMENT_CURRENTLY_UPDATING_FOR', $text));

        $name =& Piwi::CreateWidget('Entry', 'name', $comment['name']);
        $name->SetTitle(_t('GLOBAL_NAME'));

        $email =& Piwi::CreateWidget('Entry', 'email', $comment['email']);
        $email->SetStyle('direction: ltr;');
        $email->SetTitle(_t('GLOBAL_EMAIL'));

        $url =& Piwi::CreateWidget('Entry', 'url', $comment['url']);
        $url->SetStyle('direction: ltr;');
        $url->SetTitle(_t('GLOBAL_URL'));

        $ip =& Piwi::CreateWidget('Entry', 'ip', $comment['ip']);
        $ip->SetTitle(_t('GLOBAL_IP'));
        $ip->SetReadOnly(true);

        $comment =& Piwi::CreateWidget('TextArea', 'comments', $xss->defilter($comment['comments']));
        $comment->SetRows(5);
        $comment->SetColumns(60);
        $comment->SetStyle('width: 400px;');
        $comment->SetTitle(_t('BLOG_COMMENT'));

        $cancelButton =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, 'history.go(-1);');

        $submitButton =& Piwi::CreateWidget('Button', 'send', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $submitButton->SetSubmit();

        $deleteButton =& Piwi::CreateWidget('Button', 'delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $deleteButton->AddEvent(ON_CLICK, "this.form.action.value = 'DeleteComment'; this.form.submit();");

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($deleteButton);
        $buttonbox->PackStart($cancelButton);
        $buttonbox->PackStart($submitButton);

        $fieldset->Add($staticText);
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
     * Applies changes to a blog comment
     *
     * @access  public
     */
    function SaveEditComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'name', 'url', 'email', 'comments', 'ip', 'permalink', 'status'), 'post');

        $model->UpdateComment($post['id'], $post['name'], $post['url'], $post['email'], $post['comments'],
                              $post['permalink'], $post['status']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
    }

    /**
     * Deletes a blog comment
     *
     * @access  public
     */
    function DeleteComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->DeleteComment($id);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
    }

}