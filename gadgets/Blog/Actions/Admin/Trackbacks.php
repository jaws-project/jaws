<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Trackbacks extends Blog_Actions_Admin_Default
{
    /**
     * Builds the data (an array) of filtered trackbacks
     *
     * @access  public
     * @param   int     $limit      Limit of trackbacks
     * @param   string  $filter     Filter
     * @param   string  $search     Search word
     * @param   string  $status     Spam status (approved, waiting, spam)
     * @return  array   Filtered Trackbacks
     */
    function TrackbacksData($limit = 0, $filter = '', $search = '', $status = '')
    {
        $model = $this->gadget->model->loadAdmin('Trackbacks');
        return $model->GetTrackbacksDataAsArray($filter, $search, $status, $limit);
    }

    /**
     * Displays blog trackbacks manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageTrackbacks()
    {
        $this->gadget->CheckPermission('ManageTrackbacks');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Trackbacks.html');
        $tpl->SetBlock('manage_trackbacks');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar('ManageTrackbacks'));

        $tpl->SetVariable('trackbacks_where', _t('BLOG_TRACKBACK_WHERE'));
        $tpl->SetVariable('status_label', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('deleteConfirm', _t('BLOG_DELETE_MASSIVE_TRACKBACKS'));

        //Status
        $statusData = '';
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('COMMENTS_STATUS_APPROVED'), 'approved');
        $status->AddOption(_t('COMMENTS_STATUS_WAITING'), 'waiting');
        $status->AddOption(_t('COMMENTS_STATUS_SPAM'), 'spam');
        $status->SetDefault($statusData);
        $status->AddEvent(ON_CHANGE, 'return searchTrackback();');
        $tpl->SetVariable('status', $status->Get());

        // filter by
        $filterByData = '';
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('BLOG_POST_ID_IS'), 'postid');
        $filterBy->AddOption(_t('BLOG_TITLE_CONTAINS'), 'title');
        $filterBy->AddOption(_t('BLOG_TRACKBACK_EXCERPT_CONTAINS'), 'excerpt');
        $filterBy->AddOption(_t('BLOG_TRACKBACK_BLOGNAME_CONTAINS'), 'blog_name');
        $filterBy->AddOption(_t('BLOG_URL_CONTAINS'), 'url');
        $filterBy->AddOption(_t('BLOG_IP_IS'), 'ip');
        $filterBy->SetDefault($filterByData);
        $tpl->SetVariable('filter_by', $filterBy->Get());

        // filter
        $filterData = '';
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('BLOG_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchTrackback();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('blog_trackback');

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('trackbacks_box');
        $gridBox->SetStyle('width: 100%;');

        //Datagrid
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('trackbacks_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->TotalRows($total);
        $grid->useMultipleSelection();
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('BLOG_TRACKBACK_BLOGNAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_CREATED')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('trackbacks_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'trackbacks_actions');
        $actions->SetID('trackbacks_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('COMMENTS_MARK_AS_APPROVED'), 'approved');
        $actions->AddOption(_t('COMMENTS_MARK_AS_WAITING'), 'waiting');
        $actions->AddOption(_t('COMMENTS_MARK_AS_SPAM'), 'spam');

        $execute =& Piwi::CreateWidget('Button', 'executeTrackbackAction', '',
                                       STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript: trackbackDGAction(document.getElementById('trackbacks_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($grid);
        $gridBox->Add($gridForm);
        
        // Display the data
        $tpl->SetVariable('trackbacks', $gridBox->Get());
        $tpl->ParseBlock('manage_trackbacks');
        return $tpl->Get();
    }

    /**
     * Displays blog trackback to be edited
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTrackback()
    {
        $this->gadget->CheckPermission('ManageTrackbacks');

        $tModel = $this->gadget->model->loadAdmin('Trackbacks');
        $pModel = $this->gadget->model->loadAdmin('Posts');
        // Fetch the trackback
        $trackback = $tModel->GetTrackback(jaws()->request->fetch('id', 'get'));
        if (Jaws_Error::IsError($trackback)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageTrackbacks');
        }

        // Fetch the entry
        $entry = $pModel->getEntry($trackback['parent_id']);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageTrackbacks');
        }

        $tpl = $this->gadget->template->loadAdmin('Trackback.html');
        $tpl->SetBlock('view_trackback');
        $tpl->SetVariable('menubar', $this->MenuBar('ManageTrackbacks'));

        $date = Jaws_Date::getInstance();
        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('BLOG_VIEW_TRACKBACK'));

        $text = '<strong>' . $entry['title'] . '</strong>';
        $staticText =& Piwi::CreateWidget('StaticEntry', _t('BLOG_TRACKBACKS_CURRENTLY_UPDATING_FOR', $text));

        $blog_name =& Piwi::CreateWidget('Entry', 'blog_name', Jaws_XSS::filter($trackback['blog_name']));
        $blog_name->SetTitle(_t('BLOG_TRACKBACK_BLOGNAME'));
        $blog_name->SetStyle('width: 400px;');

        $url =& Piwi::CreateWidget('Entry', 'url', Jaws_XSS::filter($trackback['url']));
        $url->SetStyle('direction: ltr;');
        $url->SetTitle(_t('GLOBAL_URL'));
        $url->SetStyle('width: 400px;');

        $createTime =& Piwi::CreateWidget('Entry', 'create_time', $date->Format($trackback['createtime']));
        $createTime->SetTitle(_t('GLOBAL_CREATETIME'));
        $createTime->SetStyle('direction: ltr;');
        $createTime->SetEnabled(false);

        $updateTime =& Piwi::CreateWidget('Entry', 'update_time', $date->Format($trackback['updatetime']));
        $updateTime->SetTitle(_t('GLOBAL_UPDATETIME'));
        $updateTime->SetStyle('direction: ltr;');
        $updateTime->SetEnabled(false);

        $ip =& Piwi::CreateWidget('Entry', 'ip', $trackback['ip']);
        $ip->SetTitle(_t('GLOBAL_IP'));
        $ip->SetStyle('direction: ltr;');
        $ip->SetEnabled(false);

        $subject =& Piwi::CreateWidget('Entry', 'title', Jaws_XSS::filter($trackback['title']));
        $subject->SetTitle(_t('GLOBAL_TITLE'));
        $subject->SetStyle('width: 400px;');

        $excerpt =& Piwi::CreateWidget('TextArea', 'excerpt', $trackback['excerpt']);
        $excerpt->SetRows(5);
        $excerpt->SetColumns(60);
        $excerpt->SetStyle('width: 400px;');
        $excerpt->SetTitle(_t('BLOG_TRACKBACK_EXCERPT'));

        $cancelButton =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, 'history.go(-1);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($cancelButton);

        $fieldset->Add($staticText);
        $fieldset->Add($blog_name);
        $fieldset->Add($url);
        $fieldset->Add($createTime);
        $fieldset->Add($updateTime);
        $fieldset->Add($ip);
        $fieldset->Add($subject);
        $fieldset->Add($excerpt);

        $tpl->SetVariable('field', $fieldset->Get());
        $tpl->SetVariable('buttonbox', $buttonbox->Get());

        $tpl->ParseBlock('view_trackback');

        return $tpl->Get();
    }

}