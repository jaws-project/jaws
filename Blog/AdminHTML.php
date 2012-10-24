<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlogAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default admin action(NewEntry)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        return $this->NewEntry();
    }

    /**
     * Displays admin menu bar according to selected action
     *
     * @access  public
     * @param   string  $action_selected    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action_selected)
    {
        $actions = array('Summary', 'NewEntry', 'ListEntries',
                         'ManageComments', 'ManageTrackbacks',
                         'ManageCategories', 'AdditionalSettings');
        if (!in_array($action_selected, $actions)) {
            $action_selected = 'ListEntries';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Summary',_t('BLOG_SUMMARY'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=Summary', 'images/stock/new.png');
        if ($this->GetPermission('AddEntries')) {
            $menubar->AddOption('NewEntry', _t('BLOG_NEW_ENTRY'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=NewEntry', 'images/stock/new.png');
        }
        $menubar->AddOption('ListEntries', _t('BLOG_LIST_ENTRIES'),
                            BASE_SCRIPT . '?gadget=Blog&amp;action=ListEntries', 'images/stock/edit.png');
        if ($this->GetPermission('ManageComments')) {
            $menubar->AddOption('ManageComments', _t('BLOG_MANAGE_COMMENTS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageComments', 'images/stock/stock-comments.png');
        }
        if ($this->GetPermission('ManageTrackbacks')) {
            $menubar->AddOption('ManageTrackbacks', _t('BLOG_MANAGE_TRACKBACKS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageTrackbacks', 'images/stock/stock-comments.png');
        }
        if ($this->GetPermission('ManageCategories')) {
            $menubar->AddOption('ManageCategories', _t('BLOG_CATEGORIES'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=ManageCategories', 'images/stock/edit.png');
        }
        if ($this->GetPermission('Settings')) {
            $menubar->AddOption('AdditionalSettings', _t('BLOG_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Blog&amp;action=AdditionalSettings', 'images/stock/properties.png');
        }
        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

    /**
     * Displays blog summary with some statistics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Summary()
    {
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $summary = $model->GetSummary();
        if (Jaws_Error::IsError($summary)) {
            $summary = array();
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Summary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('menubar', $this->MenuBar('Summary'));

        // Ok, start the stats!
        $tpl->SetVariable('blog_stats', _t('BLOG_STATS'));
        // First entry

        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor(null);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_FIRST_ENTRY'));
        if (isset($summary['min_date'])) {
            $date = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('value', $date->Format($summary['min_date']));
        } else {
            $tpl->SetVariable('value', '');
        }
        $tpl->ParseBlock('summary/item');

        // Last entry
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_LAST_ENTRY'));
        if (isset($summary['max_date'])) {
            $date = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('value', $date->Format($summary['max_date']));
        } else {
            $tpl->SetVariable('value', '');
        }
        $tpl->ParseBlock('summary/item');


        // Blog entries
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_TOTAL_ENTRIES'));
        $tpl->SetVariable('value', isset($summary['qty_posts']) ? $summary['qty_posts'] : '');
        $tpl->ParseBlock('summary/item');

        // Avg. entries per week
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_AVERAGE_ENTRIES'));
        $tpl->SetVariable('value', isset($summary['AvgEntriesPerWeek']) ? $summary['AvgEntriesPerWeek'] : '');
        $tpl->ParseBlock('summary/item');


        // Comments
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_COMMENTS_RECEIVED'));
        $tpl->SetVariable('value', isset($summary['CommentsQty']) ? $summary['CommentsQty'] : '');
        $tpl->ParseBlock('summary/item');

        // Recent entries
        if (isset($summary['Entries']) && count($summary['Entries']) > 0) {
            $tpl->SetBlock('summary/recent');
            $tpl->SetVariable('title', _t('BLOG_RECENT_ENTRIES'));

            $date = $GLOBALS['app']->loadDate();
            foreach ($summary['Entries'] as $e) {
                $tpl->SetBlock('summary/recent/link');
                $url = BASE_SCRIPT . '?gadget=Blog&action=EditEntry&id='.$e['id'];
                if ($e['published'] === false) {
                    $extra = '<span style="color: #999; font-size: 10px;"> [' . _t('BLOG_DRAFT') . '] </span>';
                } else {
                    $extra = '';
                }
                $tpl->SetVariable('url',   $url);
                $tpl->SetVariable('title', $e['title']);
                $tpl->SetVariable('extra', $extra);
                $tpl->SetVariable('date',  $date->Format($e['publishtime']));
                $tpl->ParseBlock('summary/recent/link');
            }
            $tpl->ParseBlock('summary/recent');
        }

        // Recent comments
        if (isset($summary['Comments']) &&(count($summary['Comments']) > 0)) {
            $tpl->SetBlock('summary/recent');
            $tpl->SetVariable('title', _t('BLOG_RECENT_COMMENTS'));
            $date = $GLOBALS['app']->loadDate();
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            foreach ($summary['Comments'] as $c) {
                $tpl->SetBlock('summary/recent/link');
                $url = BASE_SCRIPT . '?gadget=Blog&action=EditComment&id='.$c['id'];
                $extra = "<strong style=\"color: #666;\">" . $xss->filter($c['name']) . ": </strong>";
                $tpl->SetVariable('url',   $xss->filter($url));
                $tpl->SetVariable('title', $xss->filter($c['title']));
                $tpl->SetVariable('extra', $extra);
                $tpl->SetVariable('date',  $date->Format($c['createtime']));
                $tpl->ParseBlock('summary/recent/link');
            }
            $tpl->ParseBlock('summary/recent');
        }

        $tpl->ParseBlock('summary');
        return $tpl->Get();
    }

    /**
     * Displays blog settings administration panel
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AdditionalSettings()
    {
        $this->CheckPermission('Settings');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('AdditionalSettings.html');
        $tpl->SetBlock('additional');

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('AdditionalSettings'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Blog'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveAdditionalSettings'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('BLOG_ADDITIONAL_SETTINGS'));
        // $fieldset =& Piwi::CreateWidget('FieldSet', _t('BLOG_ADDITIONAL_SETTINGS'));

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveSettings(this.form);');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $settings = $model->GetSettings();
        if (Jaws_Error::IsError($settings)) {
            $settings = array();
        }

        // Default View
        $tpl->SetVariable('label', _t('BLOG_DEFAULT_VIEW'));
        $viewCombo =& Piwi::CreateWidget('Combo', 'default_view');
        $viewCombo->setContainerClass('oneline');
        $viewCombo->SetTitle(_t('BLOG_DEFAULT_VIEW'));
        $viewCombo->AddOption(_t('BLOG_MONTHLY'), 'monthly');
        $viewCombo->AddOption(_t('BLOG_LATEST_ENTRY'), 'latest_entry');
        $viewCombo->AddOption(_t('BLOG_LAST_ENTRIES'), 'last_entries');
        $viewCombo->AddOption(_t('BLOG_DEFAULT_CATEGORY'), 'default_category');
        $viewCombo->SetDefault(isset($settings['default_view']) ?
                               $settings['default_view'] : '');

        // Last entries limit
        $limitCombo =& Piwi::CreateWidget('Combo', 'last_entries_limit');
        $limitCombo->setContainerClass('oneline');
        $limitCombo->SetTitle(_t('BLOG_LAST_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $limitCombo->AddOption($i, $i);
        }
        $limitCombo->SetDefault(isset($settings['last_entries_limit']) ?
                                $settings['last_entries_limit'] : '');

        // Popular limit
        $popCombo =& Piwi::CreateWidget('Combo', 'popular_limit');
        $popCombo->setContainerClass('oneline');
        $popCombo->SetTitle(_t('BLOG_POPULAR_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $popCombo->AddOption($i, $i);
        }
        $popCombo->SetDefault(isset($settings['popular_limit']) ?
                                $settings['popular_limit'] : '');

        // Last comments limit
        $commentslimitCombo =& Piwi::CreateWidget('Combo', 'last_comments_limit');
        $commentslimitCombo->setContainerClass('oneline');
        $commentslimitCombo->SetTitle(_t('BLOG_LAST_COMMENTS_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $commentslimitCombo->AddOption($i, $i);
        }
        $commentslimitCombo->SetDefault(isset($settings['last_comments_limit']) ?
                                        $settings['last_comments_limit'] : '');

        // Last recent comments
        $recentcommentsLimitCombo =& Piwi::CreateWidget('Combo', 'last_recentcomments_limit');
        $recentcommentsLimitCombo->setContainerClass('oneline');
        $recentcommentsLimitCombo->SetTitle(_t('BLOG_LAST_RECENTCOMMENTS_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $recentcommentsLimitCombo->AddOption($i, $i);
        }
        $recentcommentsLimitCombo->SetDefault(isset($settings['last_recentcomments_limit']) ?
                                              $settings['last_recentcomments_limit'] : '');

        $categories = $model->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            // Default category

            $catCombo =& Piwi::CreateWidget('Combo', 'default_category');
            $catCombo->setContainerClass('oneline');
            $catCombo->SetTitle(_t('BLOG_DEFAULT_CATEGORY'));
            foreach ($categories as $cat) {
                $catCombo->AddOption($cat['name'], $cat['id']);
            }
            $catCombo->SetDefault(isset($settings['default_category']) ?
                                  $settings['default_category'] : '');
        }

        // RSS/Atom limit
        $xmlCombo =& Piwi::CreateWidget('Combo', 'xml_limit');
        $xmlCombo->setContainerClass('oneline');
        $xmlCombo->SetTitle(_t('BLOG_RSS_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 50; $i += 5) {
            $xmlCombo->AddOption($i, $i);
        }
        $xmlCombo->SetDefault(isset($settings['xml_limit']) ? $settings['xml_limit'] : '');

        // Comments
        $commCombo =& Piwi::CreateWidget('Combo', 'comments');
        $commCombo->setContainerClass('oneline');
        $commCombo->SetTitle(_t('BLOG_COMMENTS'));
        $commCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $commCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $commCombo->SetDefault(isset($settings['comments']) ? $settings['comments'] : '');

        // Comment status
        $commStatusCombo =& Piwi::CreateWidget('Combo', 'comment_status');
        $commStatusCombo->setContainerClass('oneline');
        $commStatusCombo->SetTitle(_t('BLOG_DEFAULT_STATUS', _t('BLOG_COMMENTS')));
        $commStatusCombo->AddOption(_t('GLOBAL_STATUS_APPROVED'), 'approved');
        $commStatusCombo->AddOption(_t('GLOBAL_STATUS_WAITING'), 'waiting');
        $commStatusCombo->SetDefault($settings['comment_status']);

        // Trackback
        $tbCombo =& Piwi::CreateWidget('Combo', 'trackback');
        $tbCombo->setContainerClass('oneline');
        $tbCombo->SetTitle(_t('BLOG_TRACKBACK'));
        $tbCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $tbCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $tbCombo->SetDefault($settings['trackback']);

        // Trackback status
        $tbStatusCombo =& Piwi::CreateWidget('Combo', 'trackback_status');
        $tbStatusCombo->setContainerClass('oneline');
        $tbStatusCombo->SetTitle(_t('BLOG_DEFAULT_STATUS', _t('BLOG_TRACKBACK')));
        $tbStatusCombo->AddOption(_t('GLOBAL_STATUS_APPROVED'), 'approved');
        $tbStatusCombo->AddOption(_t('GLOBAL_STATUS_WAITING'), 'waiting');
        $tbStatusCombo->SetDefault($settings['trackback_status']);

        // Pingback
        $pbCombo =& Piwi::CreateWidget('Combo', 'pingback');
        $pbCombo->setContainerClass('oneline');
        $pbCombo->SetTitle(_t('BLOG_PINGBACK'));
        $pbCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $pbCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $pbCombo->SetDefault($settings['pingback']);

        $fieldset->Add($viewCombo);
        $fieldset->Add($limitCombo);
        $fieldset->Add($popCombo);
        $fieldset->Add($commentslimitCombo);
        $fieldset->Add($recentcommentsLimitCombo);
        if (!Jaws_Error::IsError($categories)) {
            $fieldset->Add($catCombo);
        }
        $fieldset->Add($xmlCombo);
        $fieldset->Add($commCombo);
        $fieldset->Add($commStatusCombo);
        $fieldset->Add($tbCombo);
        $fieldset->Add($tbStatusCombo);
        $fieldset->Add($pbCombo);
        $fieldset->SetDirection('vertical');
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('additional');

        return $tpl->Get();
    }

    /**
     * Applies modifications on blog settings
     *
     * @access  public
     */
    function SaveAdditionalSettings()
    {
        $this->CheckPermission('Settings');

        $request =& Jaws_Request::getInstance();
        $names = array(
            'default_view', 'last_entries_limit', 'last_comments_limit',
            'last_recentcomments_limit', 'default_category', 'xml_limit',
            'comments', 'comment_status', 'trackback', 'trackback_status');
        $post = $request->get($names, 'post');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->SaveSettings($post['default_view'], $post['last_entries_limit'],
                             $post['last_comments_limit'], $post['last_recentcomments_limit'],
                             $post['default_category'], $post['xml_limit'],
                             $post['comments'], $post['comment_status'],
                             $post['trackback'], $post['trackback_status']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=AdditionalSettings');
    }

    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML template of a datagrid
     */
    function CommentsDatagrid()
    {
        require_once JAWS_PATH . 'include/Jaws/Widgets/CommentUI.php';

        $commentUI = new Jaws_Widgets_CommentUI($this->_Name);
        $commentUI->SetEditAction(BASE_SCRIPT . '?gadget=Blog&amp;action=EditComment&amp;id={id}');
        return $commentUI->Get();
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
        require_once JAWS_PATH . 'include/Jaws/Widgets/CommentUI.php';

        $commentUI = new Jaws_Widgets_CommentUI($this->_Name);
        $commentUI->SetEditAction(BASE_SCRIPT . '?gadget=Blog&amp;action=EditComment&amp;id={id}');
        return $commentUI->GetDataAsArray($filter, $search, $status, $limit);
    }

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
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        return $model->GetTrackbacksDataAsArray($filter, $search, $status, $limit);
    }

    /**
     * Displays blog comments manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $this->CheckPermission('ManageComments');
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
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 'approved');
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 'waiting');
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 'spam');
        $status->SetDefault('various');
        $status->AddEvent(ON_CHANGE, 'return searchComment();');
        $tpl->SetVariable('status', $status->Get());

        // filter by
        $filterByData = '';
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('BLOG_POST_ID_IS'), 'postid');
        $filterBy->AddOption(_t('BLOG_TITLE_CONTAINS'), 'title');
        $filterBy->AddOption(_t('BLOG_COMMENT_CONTAINS'), 'comment');
        $filterBy->AddOption(_t('BLOG_NAME_CONTAINS'), 'name');
        $filterBy->AddOption(_t('BLOG_EMAIL_CONTAINS'), 'email');
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
        $this->CheckPermission('ManageComments');
        $request =& Jaws_Request::getInstance();

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        // Fetch the comment
        $comment = $model->GetComment($request->get('id', 'get'));
        if (Jaws_Error::IsError($comment)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
        }

        // Fetch the entry
        ///FIXME we need to either create a query for this or make getEntry only fetch the title, this is a overkill atm
        $entry = $model->getEntry($comment['gadget_reference']);
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
        $permalink = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $comment['gadget_reference']));
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

        $subject =& Piwi::CreateWidget('Entry', 'title', $comment['title']);
        $subject->SetTitle(_t('GLOBAL_TITLE'));
        $subject->SetStyle('width: 400px;');

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
        $fieldset->Add($subject);
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
        $this->CheckPermission('ManageComments');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'name', 'title', 'url', 'email', 'comments', 'ip', 'permalink', 'status'), 'post');

        $model->UpdateComment($post['id'], $post['name'], $post['title'],
                              $post['url'], $post['email'], $post['comments'],
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
        $this->CheckPermission('ManageComments');
        $model   = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $request =& Jaws_Request::getInstance();

        $get_id  = $request->get('id', 'get');
        $post_id = $request->get('id', 'post');

        if (!is_null($get_id)) {
            $model->DeleteComment($get_id);
        } elseif(!is_null($post_id)) {
            $model->DeleteComment($post_id);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageComments');
    }

    /**
     * Displays blog trackbacks manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageTrackbacks()
    {
        $this->CheckPermission('ManageTrackbacks');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ManageTrackbacks.html');
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
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 'approved');
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 'waiting');
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 'spam');
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

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
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
        $actions->AddOption('', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('GLOBAL_MARK_AS_APPROVED'), 'approved');
        $actions->AddOption(_t('GLOBAL_MARK_AS_WAITING'), 'waiting');
        $actions->AddOption(_t('GLOBAL_MARK_AS_SPAM'), 'spam');

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
        $this->CheckPermission('ManageTrackbacks');
        $request =& Jaws_Request::getInstance();

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        // Fetch the trackback
        $trackback = $model->GetTrackback($request->get('id', 'get'));
        if (Jaws_Error::IsError($trackback)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageTrackbacks');
        }

        // Fetch the entry
        $entry = $model->getEntry($trackback['parent_id']);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageTrackbacks');
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ViewTrackback.html');
        $tpl->SetBlock('view_trackback');
        $tpl->SetVariable('menubar', $this->MenuBar('ManageTrackbacks'));

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $date = $GLOBALS['app']->loadDate();


        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('BLOG_VIEW_TRACKBACK'));

        $text = '<strong>' . $entry['title'] . '</strong>';
        $staticText =& Piwi::CreateWidget('StaticEntry', _t('BLOG_TRACKBACKS_CURRENTLY_UPDATING_FOR', $text));

        $blog_name =& Piwi::CreateWidget('Entry', 'blog_name', $xss->filter($trackback['blog_name']));
        $blog_name->SetTitle(_t('BLOG_TRACKBACK_BLOGNAME'));
        $blog_name->SetStyle('width: 400px;');

        $url =& Piwi::CreateWidget('Entry', 'url', $xss->filter($trackback['url']));
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

        $subject =& Piwi::CreateWidget('Entry', 'title', $xss->filter($trackback['title']));
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

    /**
     * Get a list of categories in a combo
     *
     * @access   public
     * @param    array   $categories    Array of categories (optional)
     * @return   string  XHTML of a Combo
     */
    function GetCategoriesAsCombo($categories = null)
    {
        if (!is_array($categories)) {
            $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
            $categories = $model->GetCategories();
        }

        $combo =& Piwi::CreateWidget('Combo', 'category_id');
        $combo->SetID('category_id');
        $combo->SetStyle('width: 100%; margin-bottom: 10px;');
        $combo->SetSize(18);
        $combo->AddEvent(ON_CHANGE, 'editCategory(this.value)');

        foreach($categories as $cat) {
            $combo->AddOption($cat['name'], $cat['id']);
        }
        return $combo->Get();
    }


    /**
     * Get the categories form
     *
     * @access  public
     * @param   string  $second_action  
     * @param   int     $id             Category id
     * @return  string  XHTML template content
     */
    function CategoryForm($second_action = 'new', $id = '')
    {
        //Category form:
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Blog'));

        $name          = '';
        $description   = '';
        $fast_url      = '';
        $meta_keywords = '';
        $meta_desc     = '';
        if ($second_action == 'editcategory') {
            $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
            $item = $model->GetCategory($id);
            $name          = (isset($item['name'])) ? $item['name'] : '';
            $description   = (isset($item['description'])) ? $item['description'] : '';
            $fast_url      = (isset($item['fast_url'])) ? $item['fast_url'] : '';
            $meta_keywords = (isset($item['meta_keywords'])) ? $item['meta_keywords'] : '';
            $meta_desc     = (isset($item['meta_description'])) ? $item['meta_description'] : '';
        }

        $action = $second_action == 'editcategory' ? 'UpdateCategory' : 'AddCategory';
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', $action));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'catid', $id));

        $text = $second_action == 'editcategory' ? _t('BLOG_EDIT_CATEGORY') : _t('BLOG_NEW_CATEGORY');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet($text);
        // $fieldset =& Piwi::CreateWidget('FieldSet', $text);
        $fieldset->SetDirection('vertical');

        $catName =& Piwi::CreateWidget('Entry', 'name', $name);
        $catName->SetTitle(_t('BLOG_CATEGORY'));
        $catName->setStyle('width: 250px;');

        $catFastURL =& Piwi::CreateWidget('Entry', 'fast_url', $fast_url);
        $catFastURL->SetTitle(_t('BLOG_FASTURL'));
        $catFastURL->setStyle('width: 250px;');

        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', $meta_keywords);
        $metaKeywords->SetTitle(_t('GLOBAL_META_KEYWORDS'));
        $metaKeywords->setStyle('width: 250px;');

        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', $meta_desc);
        $metaDesc->SetTitle(_t('GLOBAL_META_DESCRIPTION'));
        $metaDesc->setStyle('width: 250px;');

        $catDescription =& Piwi::CreateWidget('TextArea', 'description', $description);
        $catDescription->SetTitle(_t('GLOBAL_DESCRIPTION'));
        $catDescription->setStyle('width: 250px;');

        $fieldset->Add($catName);
        $fieldset->Add($catFastURL);
        $fieldset->Add($metaKeywords);
        $fieldset->Add($metaDesc);
        $fieldset->Add($catDescription);
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');

        if ($second_action == 'editcategory') {
            $deletemenu =& Piwi::CreateWidget('Button', 'deletecategory', _t('GLOBAL_DELETE'), STOCK_DELETE);
            $deletemenu->AddEvent(ON_CLICK, "javascript: if (confirm('"._t('BLOG_DELETE_CONFIRM_CATEGORY')."')) ".
                                  "deleteCategory(this.form);");
            $buttonbox->Add($deletemenu);
        }

        $cancelmenu =& Piwi::CreateWidget('Button', 'cancelcategory', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelmenu->AddEvent(ON_CLICK, 'javascript: resetCategoryForm();');
        $buttonbox->Add($cancelmenu);

        $save =& Piwi::CreateWidget('Button', 'save',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveCategory(this.form);');
        $buttonbox->PackStart($save);

        $form->Add($buttonbox);

        return $form->Get();
    }

    /**
     * Displays blog categories manager
     *
     * @access  public
     * @param   string  $second_action      
     * @return  string  XHTML template content
     */
    function ManageCategories($second_action = '')
    {
        $this->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ManageCategories.html');
        $tpl->SetBlock('categories');

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('ManageCategories'));

        $tpl->SetBlock('categories/manage');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('categories', _t('BLOG_CATEGORIES'));

        //Category form:
        $tpl->SetVariable('new_form', $this->CategoryForm('new'));
        $tpl->SetVariable('delete_message',_t('BLOG_DELETE_CONFIRM_CATEGORY'));
        $tpl->SetVariable('combo', $this->GetCategoriesAsCombo());

        $new =& Piwi::CreateWidget('Button', 'new',_t('BLOG_NEW_CATEGORY'), STOCK_NEW);
        $new->SetStyle('width: 100%;');
        $new->AddEvent(ON_CLICK, 'javascript: newCategory();');
        $tpl->SetVariable('new_button', $new->Get());

        $tpl->ParseBlock('categories/manage');
        $tpl->ParseBlock('categories');

        return $tpl->Get();
    }

    /**
     * Adds the given category to blog
     *
     * @access  public
     */
    function AddCategory()
    {
        $request =& Jaws_Request::getInstance();

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->NewCategory($request->get('catname', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

    /**
     * Updates a blog category name
     *
     * @access  public
     */
    function UpdateCategory()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('catid', 'catname'), 'post');

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->UpdateCategory($post['catid'], $post['catname']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=EditCategory&id=' . $post['catid']);
    }

    /**
     * Deletes the given blog category
     *
     * @access  public
     */
    function DeleteCategory()
    {
        $request =& Jaws_Request::getInstance();

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->DeleteCategory($request->get('catid', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

    /**
     * Displays an editor to write a new blog entry or preview it before saving
     *
     * @access  public
     * @param   string $action  "preview" or empty(optional, empty by default)
     * @return  string  XHTML template content
     */
    function NewEntry($action = '')
    {
        $this->CheckPermission('AddEntries');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('AdminEntry.html');
        $tpl->SetBlock('edit_entry');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('NewEntry'));

        // Title
        $tpl->SetVariable('title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('action', 'NewEntry');
        $tpl->SetVariable('id', 0);
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 95%');
        $titleEntry->setId('title');
        $tpl->SetVariable('title_field', $titleEntry->Get());

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        // Category
        $catChecks =& Piwi::CreateWidget('CheckButtons', 'categories', 'vertical');
        $categories = $model->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $a) {
                $catChecks->AddOption($a['name'], $a['id']);
            }
        }
        $catDefault = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/Blog/default_category'));
        $catChecks->SetDefault($catDefault);
        $catChecks->SetColumns(3);

        $tpl->SetVariable('category', _t('GLOBAL_CATEGORY'));
        $tpl->SetVariable('category_field', $catChecks->Get());

        // Summary
        $tpl->SetVariable('lbl_summary', _t('BLOG_ENTRY_SUMMARY'));
        $summary =& $GLOBALS['app']->LoadEditor('Blog', 'summary_block', '', false);
        $summary->setId('summary_block');
        $summary->TextArea->SetRows(8);
        $summary->TextArea->SetStyle('width: 100%;');
        $summary->SetWidth('96%');
        $tpl->SetVariable('summary', $summary->Get());

        // Body
        $tpl->SetVariable('text', _t('BLOG_ENTRY_BODY'));
        $editor =& $GLOBALS['app']->LoadEditor('Blog', 'text_block', '', false);
        $editor->setId('text_block');
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('96%');
        $tpl->SetVariable('editor', $editor->Get());

        // Allow Comments
        $allow = $GLOBALS['app']->Registry->Get('/gadgets/Blog/allow_comments') == 'true';
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption(_t('BLOG_ALLOW_COMMENTS'), 'comments', 'allow_comments', $allow);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', _t('GLOBAL_STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('BLOG_DRAFT'), '0');
        $statCombo->AddOption(_t('BLOG_PUBLISHED'), '1');
        if ($this->GetPermission('PublishEntries')) {
            $statCombo->SetDefault('1');
        } else {
            $statCombo->SetDefault('0');
            $statCombo->SetEnabled(false);
        }
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Save
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "javascript: if(this.form.elements['title'].value == '') { alert('".
                              _t('BLOG_MISSING_TITLE') . "'); this.form.elements['title'].focus(); } ".
                              "else { this.form.submit(); }");
        $tpl->SetVariable('save_button', $saveButton->Get());

        // Preview
        // TODO: We need a different stock icon for this.
        $previewButton =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $previewButton->AddEvent(ON_CLICK, "javascript: parseText(this.form);");

        $tpl->SetVariable('preview_button', $previewButton->Get());

        // Advanced stuff..
        $tpl->SetBlock('edit_entry/advanced');
        $advancedDefault = false;
        $tpl->SetVariable('advanced_style', 'display: none;');

        $editAdvancedchk =& Piwi::CreateWidget('CheckButtons', 'edit_advanced');
        $editAdvancedchk->SetID('advanced_toggle');
        $editAdvancedchk->AddOption(_t('BLOG_ADVANCED_MODE'), 'advanced', false, $advancedDefault);
        $editAdvancedchk->AddEvent(ON_CLICK, 'toggleAdvanced(this.checked);');
        $tpl->SetVariable('advanced_field', $editAdvancedchk->Get());

        $tpl->SetVariable('timestamp_label', _t('BLOG_EDIT_TIMESTAMP'));
        $tsChk =& Piwi::CreateWidget('CheckButtons', 'edit_timestamp');
        $tsChk->AddOption('', 'yes', 'edit_timestamp', false);
        $tsChk->AddEvent(ON_CLICK, 'toggleUpdate(this.checked);');
        $tpl->SetVariable('timestamp_check', $tsChk->Get());

        // Maybe we need to get date from MDB2
        $objDate = $GLOBALS['app']->loadDate();
        $now = $objDate->Format(time(), 'Y-m-d H:i:s');
        $pubdate =& Piwi::CreateWidget('DatePicker', 'pubdate', $now);
        $pubdate->SetId('pubdate');
        $pubdate->showTimePicker(true);
        $pubdate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $pubdate->setLanguageCode($GLOBALS['app']->Registry->Get('/config/calendar_language'));
        $pubdate->setCalType($GLOBALS['app']->Registry->Get('/config/calendar_type'));
        $tpl->SetVariable('pubdate', $pubdate->Get());

        $tpl->SetVariable('fasturl', _t('BLOG_FASTURL'));
        $fastUrlEntry =& Piwi::CreateWidget('Entry', 'fasturl', '');
        $fastUrlEntry->SetId('fasturl');
        $fastUrlEntry->SetTitle(_t('BLOG_FASTURL_COMMENT'));
        $fastUrlEntry->SetStyle('width: 100%; direction: ltr;');
        $tpl->SetVariable('fasturl_field', $fastUrlEntry->Get());

        $tpl->SetVariable('meta_keywords_label', _t('GLOBAL_META_KEYWORDS'), '');
        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $metaKeywords->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $tpl->SetVariable('meta_desc_label', _t('GLOBAL_META_DESCRIPTION'), '');
        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $metaDesc->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback') == 'true') {
            $tpl->SetBlock('edit_entry/advanced/trackback');
            $tpl->SetVariable('trackback_to', _t('BLOG_TRACKBACK'));
            $tb =& Piwi::CreateWidget('TextArea', 'trackback_to', '');
            $tb->SetId('trackback_to');
            $tb->SetRows(4);
            $tb->SetColumns(30);
            $tb->SetStyle('width: 99%; direction: ltr; white-space: nowrap;');
            $tpl->SetVariable('trackbackTextArea', $tb->Get());
            $tpl->ParseBlock('edit_entry/advanced/trackback');
        }
        $tpl->ParseBlock('edit_entry/advanced');
        $tpl->ParseBlock('edit_entry');
        return $tpl->Get();
    }

    /**
     * Saves a new blog entry and displays the entries list on admin section
     *
     * @access  public
     */
    function SaveNewEntry()
    {
        $this->CheckPermission('AddEntries');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $names   = array('edit_timestamp', 'pubdate', 'categories', 'title',
                         'fasturl', 'allow_comments', 'published',
                         'trackback_to', 'meta_keywords', 'meta_desc');
        $post    = $request->get($names, 'post');
        $content = $request->get(array('summary_block', 'text_block'), 'post', false);
        $post['trackback_to'] = str_replace("\r\n", "\n", $post['trackback_to']);

        $pubdate = null;
        if (isset($post['edit_timestamp']) && $post['edit_timestamp'][0] == 'yes') {
            $pubdate = $post['pubdate'];
        }

        $id = $model->NewEntry($GLOBALS['app']->Session->GetAttribute('user') , $post['categories'],
                               $post['title'], $content['summary_block'], $content['text_block'],
                               $post['fasturl'], $post['meta_keywords'], $post['meta_desc'],
                               isset($post['allow_comments'][0]), $post['trackback_to'],
                               $post['published'], $pubdate);

        if (!Jaws_Error::IsError($id)) {
            if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->GetURLFor('SingleView', array('id' => $id), true, 'site_url');
                $title = $post['title'];
                $text = $content['text_block'];
                if ($GLOBALS['app']->UTF8->strlen($text) > 250) {
                    $text = $GLOBALS['app']->UTF8->substr($text, 0, 250) . '...';
                }
                $model->SendTrackback($title, $text, $link, $to);
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ListEntries');
    }

    /**
     * Displays a preview of a new entry before saving
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PreviewNewEntry()
    {
        $this->CheckPermission('AddEntries');
        return $this->NewEntry('preview');
    }

    /**
     * Displays an editor to edit an existing blog entry or preview it before saving changes
     *
     * @access  public
     * @param   string  $action     "preview" or empty(optional, empty by default)
     * @param   int     $id         
     * @return  string  XHTML template content
     */
    function EditEntry($action = '', $id = null)
    {
        $request =& Jaws_Request::getInstance();
        $names   = array('id', 'action');
        $get     = $request->get($names, 'get');
        $names   = array('allow_comments', 'edit_advanced');
        $post    = $request->get($names, 'post');

        $id = !is_null($id) ? $id : $get['id'];
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entry = $model->GetEntry($id);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Error::Fatal('Post not found', __FILE__, __LINE__);
        }

        if ($GLOBALS['app']->Session->GetAttribute('user') != $entry['user_id']) {
            $this->CheckPermission('ModifyOthersEntries');
        }

        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('AdminEntry.html');
        $tpl->SetBlock('edit_entry');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        // Header
        $action = isset($get['action']) ? $get['action'] : null;
        $tpl->SetVariable('menubar', $this->MenuBar($action));

        // Title
        $tpl->SetVariable('title', _t('BLOG_TITLE'));
        $tpl->SetVariable('action', 'EditEntry');
        $tpl->SetVariable('id', $id);

        $titleEntry =& Piwi::CreateWidget('Entry', 'title', $entry['title']);
        $titleEntry->SetStyle('width: 95%');
        $tpl->SetVariable('title_field', $titleEntry->Get());

        // Category
        $catChecks =& Piwi::CreateWidget('CheckButtons', 'categories', 'vertical');
        $categories = $model->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $a) {
                $catChecks->AddOption($a['name'], $a['id']);
            }
        }
        $catDefault = array();
        if (!Jaws_Error::isError($entry['categories'])) {
            foreach ($entry['categories'] as $cat) {
                $catDefault[] = $cat['id'];
            }
        }
        $catChecks->SetDefault($catDefault);
        $catChecks->SetColumns(3);

        $tpl->SetVariable('category', _t('GLOBAL_CATEGORY'));
        $tpl->SetVariable('category_field', $catChecks->Get());

        // for compatibility with old versions
        $more_pos = Jaws_UTF8::strpos($entry['text'], '[more]');
        if ($more_pos !== false) {
            $entry['summary'] = Jaws_UTF8::substr($entry['text'], 0, $more_pos);
            $entry['text']    = Jaws_UTF8::str_replace('[more]', '', $entry['text']);
        }

        // Summary
        $tpl->SetVariable('lbl_summary', _t('BLOG_ENTRY_SUMMARY'));
        $summary =& $GLOBALS['app']->LoadEditor('Blog', 'summary_block', $entry['summary'], false);
        $summary->setId('summary_block');
        $summary->TextArea->SetRows(8);
        $summary->TextArea->SetStyle('width: 100%;');
        $summary->SetWidth('96%');
        $tpl->SetVariable('summary', $summary->Get());

        // Body
        $tpl->SetVariable('text', _t('BLOG_BODY'));
        $editor =& $GLOBALS['app']->LoadEditor('Blog', 'text_block', $entry['text'], false);
        $editor->setId('text_block');
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('96%');
        $tpl->SetVariable('editor', $editor->Get());

        // Allow Comments
        if (isset($post['allow_comments'])) {
            $allow = true;
        } else if (isset($entry['allow_comments']) && $entry['allow_comments'] === true) {
            $allow = true;
        } else {
            $allow = false;
        }

        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption(_t('BLOG_ALLOW_COMMENTS'), 'comments', 'allow_comments', $allow);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', _t('GLOBAL_STATUS'));
        $entry['published'] = ($entry['published'] === true) ? 1 : 0;
        $statData = $entry['published'];
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->AddOption(_t('BLOG_DRAFT'), '0');
        $statCombo->AddOption(_t('BLOG_PUBLISHED'), '1');
        $statCombo->SetDefault($statData);
        if (!$this->GetPermission('PublishEntries')) {
            $statCombo->SetEnabled(false);
        }
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Save
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('BLOG_UPDATE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "javascript: if(this.form.elements['title'].value == '') { alert('".
                                _t('BLOG_MISSING_TITLE') . "'); this.form.elements['title'].focus(); } ".
                                "else { this.form.submit(); }");
        $tpl->SetVariable('save_button', $saveButton->Get());

        // Preview
        // TODO: We need a different stock icon for this.
        $previewButton =& Piwi::CreateWidget('Button', 'preview',
                                                _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $previewButton->SetID('preview_button');
        $previewButton->AddEvent(ON_CLICK, "javascript: parseText(this.form);");
        $tpl->SetVariable('preview_button', $previewButton->Get());

        $tpl->SetBlock('edit_entry/advanced');
        $advancedDefault = false;
        if (isset($post['edit_advanced'])) {
            $advancedDefault = true;
            $tpl->SetVariable('advanced_style', 'display: inline;');
        } else {
            $tpl->SetVariable('advanced_style', 'display: none;');
        }

        $editAdvancedchk =& Piwi::CreateWidget('CheckButtons', 'edit_advanced');
        $editAdvancedchk->SetID('advanced_toggle');
        $editAdvancedchk->AddOption(_t('BLOG_ADVANCED_MODE'), 'advanced', false, $advancedDefault);
        $editAdvancedchk->AddEvent(ON_CLICK, 'toggleAdvanced(this.checked);');
        $tpl->SetVariable('advanced_field', $editAdvancedchk->Get());

        $tpl->SetVariable('timestamp_label', _t('BLOG_EDIT_TIMESTAMP'));
        $tsChk =& Piwi::CreateWidget('CheckButtons', 'edit_timestamp');
        $tsChk->AddOption('', 'yes', 'edit_timestamp', false);
        $tsChk->AddEvent(ON_CLICK, 'toggleUpdate(this.checked);');
        $tpl->SetVariable('timestamp_check', $tsChk->Get());

        $objDate = $GLOBALS['app']->loadDate();
        $pubTime = $objDate->Format($entry['publishtime'], 'Y-m-d H:i:s');
        $pubdate =& Piwi::CreateWidget('DatePicker', 'pubdate', $pubTime);
        $pubdate->SetId('pubdate');
        $pubdate->showTimePicker(true);
        $pubdate->setDateFormat('%Y-%m-%d %H:%M:%S');
        $pubdate->setLanguageCode($GLOBALS['app']->Registry->Get('/config/calendar_language'));
        $pubdate->setCalType($GLOBALS['app']->Registry->Get('/config/calendar_type'));
        $tpl->SetVariable('pubdate', $pubdate->Get());

        $tpl->SetVariable('fasturl', _t('BLOG_FASTURL'));
        $fastUrlData = $entry['fast_url'];
        $fastUrlEntry =& Piwi::CreateWidget('Entry', 'fasturl', $fastUrlData);
        $fastUrlEntry->SetId('fasturl');
        $fastUrlEntry->SetStyle('width: 100%');
        $tpl->SetVariable('fasturl_field', $fastUrlEntry->Get());

        $tpl->SetVariable('meta_keywords_label', _t('GLOBAL_META_KEYWORDS'));
        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', $entry['meta_keywords']);
        $metaKeywords->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $tpl->SetVariable('meta_desc_label', _t('GLOBAL_META_DESCRIPTION'));
        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', $entry['meta_description']);
        $metaDesc->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        // Trackback
        if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback') == 'true') {
            $tpl->SetBlock('edit_entry/advanced/trackback');
            $tpl->SetVariable('trackback_to', _t('BLOG_TRACKBACK'));
            $tb =& Piwi::CreateWidget('TextArea', 'trackback_to', $entry['trackbacks']);
            $tb->SetId('trackback_to');
            $tb->SetRows(4);
            $tb->SetColumns(30);
            // TODO: Remove this nasty hack, and replace it with some padding in the template.
            $tb->SetStyle('width: 99%; direction: ltr; white-space: nowrap;');
            $tpl->SetVariable('trackbackTextArea', $tb->Get());
            $tpl->ParseBlock('edit_entry/advanced/trackback');
        }
        $tpl->ParseBlock('edit_entry/advanced');

        $tpl->ParseBlock('edit_entry');
        return $tpl->Get();
    }

    /**
     * Displays a preview of an edited blog entry before saving changes
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function PreviewEditEntry()
    {
        $request =& Jaws_Request::getInstance();
        return $this->EditEntry('preview', $request->get('id', 'post'));
    }

    /**
     * Save changes on an edited blog entry and shows the entries list on admin section
     *
     * @access  public
     */
    function SaveEditEntry()
    {
        $request =& Jaws_Request::getInstance();
        $names   = array('id', 'edit_timestamp', 'pubdate', 'categories', 'title',
                         'fasturl', 'meta_keywords', 'meta_desc', 
                         'allow_comments', 'published', 'trackback_to');
        $post    = $request->get($names, 'post');
        $content = $request->get(array('summary_block', 'text_block'), 'post', false);

        $post['trackback_to'] = str_replace("\r\n", "\n", $post['trackback_to']);

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $id = (int)$post['id'];

        $pubdate = null;
        if (isset($post['edit_timestamp']) && $post['edit_timestamp'][0] == 'yes') {
            $pubdate = $post['pubdate'];
        }

        $model->UpdateEntry($id, $post['categories'], $post['title'], $content['summary_block'], $content['text_block'],
                            $post['fasturl'], $post['meta_keywords'], $post['meta_desc'],
                            isset($post['allow_comments'][0]), $post['trackback_to'], $post['published'], $pubdate);
        if (!Jaws_Error::IsError($id)) {
            if ($GLOBALS['app']->Registry->Get('/gadgets/Blog/trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->GetURLFor('SingleView', array('id' => $id), true, 'site_url');
                $title = $post['title'];
                $text = $content['text_block'];
                if ($GLOBALS['app']->UTF8->strlen($text) > 250) {
                    $text = $GLOBALS['app']->UTF8->substr($text, 0, 250) . '...';
                }
                $model->SendTrackback($title, $text, $link, $to);
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=EditEntry&id=' . $id);
    }

    /**
     * Shows confirm. screen for deleting a blog entry or deletes it if confirm. was done
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteEntry()
    {
        $this->CheckPermission('DeleteEntries');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'step'), 'post');

        if (!is_null($post['step']) && $post['step'] == 'delete') {
            // Delete Post
            $res = $model->DeleteEntry($post['id']);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ENTRY_DELETED'), RESPONSE_NOTICE);
            }

            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ListEntries');
        }

        $get = $request->get(array('id', 'action'), 'get');

        // Ask for confirmation...
        $entry = $model->GetEntry($get['id']);
        if (Jaws_Error::IsError($entry)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_DOES_NOT_EXISTS'));
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ListEntries');
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('DeleteEntry.html');
        $tpl->SetBlock('delete_entry');

        $tpl->SetVariable('base_script', BASE_SCRIPT);

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar($get['action']));

        // Message
        $tpl->SetVariable('delete_message', _t('BLOG_DELETE_CONFIRM_ENTRY'));

        // Delete
        $deleteButton =& Piwi::CreateWidget('Button', 'delete',
                                            _t('GLOBAL_DELETE'), STOCK_DELETE);
        $deleteButton->SetSubmit();
        $tpl->SetVariable('delete_button', $deleteButton->Get());

        // Cancel
        $cancelButton =& Piwi::CreateWidget('Button', 'cancel',
                                            _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, "javascript: this.form.action.value = 'ListEntries'; this.form.submit(); ");
        $tpl->SetVariable('cancel_button', $cancelButton->Get());

        // ID
        $idHidden =& Piwi::CreateWidget('HiddenEntry', 'id', $get['id']);

        $tpl->SetVariable('id', $idHidden->Get());
        $tpl->SetVariable('title', $entry['title']);
        $tpl->SetVariable('text', $this->ParseText($entry['text'], 'Blog'));
        $tpl->SetVariable('user', $entry['username']);
        $date = $GLOBALS['app']->loadDate();
        $tpl->SetVariable('createtime', $date->Format($entry['publishtime']));
        $pos = 1;
        $categories = '';
        foreach ($entry['categories'] as $cat) {
            $categories .= $cat['name'];
            if ($pos != count($entry['categories'])) {
                $categories .= ', ';
            }
            $pos++;
        }
        $tpl->SetVariable('category', $categories);
        $tpl->ParseBlock('delete_entry');

        return $tpl->Get();
    }

    /**
     * Displays a list of blog entries for the blog admin section
     *
     * @access  public
     * @return  string XHTML template content
     */
    function ListEntries()
    {
        $this->AjaxMe('script.js');

        $common_url = BASE_SCRIPT . '?gadget=Blog';

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ListEntries.html');
        $tpl->SetBlock('list_entries');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('deleteConfirm', _t('BLOG_DELETE_MASSIVE_ENTRIES'));

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('ListEntries'));

        // Filtering
        // Show past n days etc.
        $showCombo =& Piwi::CreateWidget('Combo', 'show');
        $showCombo->setId('show');
        $showCombo->AddOption('&nbsp;', 'NOTHING');
        $showCombo->AddOption(_t('BLOG_RECENT_POSTS'), 'RECENT');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $monthentries = $model->GetMonthsEntries();
        if (!Jaws_Error::IsError($monthentries) && is_array($monthentries)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($monthentries as $e) {
                $showCombo->AddOption($date->MonthString($e['month']).' '.$e['year'],
                                      $e['month'].':'.$e['year']);
            }
        }
        $showCombo->AddEvent(ON_CHANGE, 'javascript: searchPost();');
        $show = 'NOTHING';
        $showCombo->SetDefault('NOTHING');

        $tpl->SetVariable('show', _t('BLOG_SHOW'));
        $tpl->SetVariable('show_field', $showCombo->Get());

        // Category filter
        $category = '';
        $catCombo =& Piwi::CreateWidget('Combo', 'category');
        $catCombo->setId('category');
        $catCombo->AddOption('&nbsp;', '');
        $categories = $model->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $cat) {
                $name = $cat['name'];
                $catCombo->AddOption($name, $cat['id']);
            }
        }

        $catCombo->SetDefault($category);
        $catCombo->AddEvent(ON_CHANGE, 'javascript: searchPost();');
        $tpl->SetVariable('category', _t('BLOG_CATEGORY'));
        $tpl->SetVariable('category_field', $catCombo->Get());

        // Status filter
        $status = '';
        $statusCombo =& Piwi::CreateWidget('Combo', 'status');
        $statusCombo->setId('status');
        $statusCombo->AddOption('&nbsp;', '');
        $statusCombo->AddOption(_t('BLOG_PUBLISHED'), '1');
        $statusCombo->AddOption(_t('BLOG_DRAFT'), '0');
        $statusCombo->SetDefault($status);
        $statusCombo->AddEvent(ON_CHANGE, 'javascript: searchPost();');
        $tpl->SetVariable('status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status_field', $statusCombo->Get());

        $catCombo->SetDefault($category);
        $catCombo->AddEvent(ON_CHANGE, 'javascript: searchPost();');
        $tpl->SetVariable('category', _t('BLOG_CATEGORY'));
        $tpl->SetVariable('category_field', $catCombo->Get());

        // Free text search
        $searchButton =& Piwi::CreateWidget('Button', 'searchButton', _t('GLOBAL_SEARCH'), STOCK_SEARCH);
        $searchButton->AddEvent(ON_CLICK, 'javascript: searchPost();');
        $tpl->SetVariable('search', $searchButton->Get());

        $search = '';
        $searchEntry =& Piwi::CreateWidget('Entry', 'search', $search);
        $tpl->SetVariable('search_field', $searchEntry->Get());

        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('entries_box');
        $gridBox->SetStyle('width: 100%;');

        $grid =& Piwi::CreateWidget('DataGrid', array(), null);
        $grid->SetID('posts_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->TotalRows($model->TotalOfPosts());
        $grid->useMultipleSelection();
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('BLOG_EDIT_TIMESTAMP')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_UPDATETIME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('BLOG_AUTHOR')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('entries_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'entries_actions');
        $actions->SetID('entries_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('', '');
        $actions->AddOption(_t('GLOBAL_DELETE'),  'delete');
        $actions->AddOption(_t('BLOG_DRAFT'),     '0');
        $actions->AddOption(_t('BLOG_PUBLISHED'), '1');

        $execute =& Piwi::CreateWidget('Button', 'executeEntryAction', '', STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript: entryDGAction(document.getElementById('entries_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($grid);
        $gridBox->Add($gridForm);
        $tpl->SetVariable('entries', $gridBox->Get());

        $tpl->ParseBlock('list_entries');
        return $tpl->Get();
    }

    /**
     * Wrapper functions for EditCategory action
     * 
     * @access  public
     * @return  string  XHTML template content
     */
    function EditCategory()
    {
        return $this->ManageCategories('editcategory');
    }

    /**
     * Prepares the datagrid for blog posts
     *
     * @access  public
     * @return  void
     */
    function PostsDatagrid()
    {
    }

    /**
     * Prepares the data of an advanced search on blog posts
     *
     * @access  public
     * @param   string  $period     Period to look for
     * @param   int     $cat        Category
     * @param   int     $status     Status (0=Draft, 1=Published)
     * @param   string  $search     Search word
     * @param   int     $limit      Limit data
     * @return  array   An array with all the data
     */
    function PostsData($period, $cat, $status, $search, $limit = 0)
    {
        $common_url = BASE_SCRIPT . '?gadget=Blog';

        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $entries = $model->AdvancedSearch($limit, $period, $cat, $status, $search,
                                          $GLOBALS['app']->Session->GetAttribute('user'));

        if (Jaws_Error::IsError($entries)) {
            return array();
        }

        $posts = array();
        $date = $GLOBALS['app']->loadDate();

        foreach ($entries as $row) {
            $post = array();
            $id = $row['id'];
            $post['__KEY__'] = $id;
            $post['title'] = '<a href="'.$common_url.'&amp;action=EditEntry&amp;id='.$id.'">'.
                $row['title'].'</a>';
            $post['publishtime'] = $date->Format($row['publishtime']);
            $post['updatetime']  = $date->Format($row['updatetime']);
            $post['username']    = $row['username'];
            $post['published']   = ($row['published'] === true) ? _t('BLOG_PUBLISHED') : _t('BLOG_DRAFT');

            $actions = '';
            $link = Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                       $common_url.'&amp;action=EditEntry&amp;id='.$id,
                                       STOCK_EDIT);
            $actions = $link->Get().'&nbsp;';

            if ($this->GetPermission('ManageComments')) {
                $link = Piwi::CreateWidget('Link', _t('BLOG_COMMENTS'),
                                           $common_url.'&amp;action=ManageComments&amp;filterby=postid&amp;filter='.$id,
                                           'images/stock/stock-comments.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->GetPermission('DeleteEntries')) {
                $link = Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                           $common_url.'&amp;action=DeleteEntry&amp;id='.$id,
                                           STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $post['actions'] = $actions;
            $posts[] = $post;
        }

        unset($entries);
        return $posts;
    }

    /**
     * Format a status boolean to human readable
     *
     * @access  public
     * @param   string $value value to format
     * @return  string  ("Published" or "Draft")
     */
    function FormatStatus($value)
    {
        return ($value === true) ? _t('BLOG_PUBLISHED') : _t('BLOG_DRAFT');
    }

    /**
     * Format a date using Jaws
     *
     * @access  public
     * @param   string  $value  The data to format.
     * @return  string The formatted date.
     */
    function FormatDate($value)
    {
        $date = $GLOBALS['app']->loadDate();
        return $date->Format($value);
    }
}
