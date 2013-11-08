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
class Blog_Actions_Admin_Entries extends Blog_Actions_Admin_Default
{
    /**
     * Displays an editor to write a new blog entry or preview it before saving
     *
     * @access  public
     * @param   string $action  "preview" or empty(optional, empty by default)
     * @return  string  XHTML template content
     */
    function NewEntry($action = '')
    {
        $this->gadget->CheckPermission('AddEntries');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadAdminTemplate('Entry.html');
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

        $model = $this->gadget->loadModel('Categories');
        // Category
        $catChecks =& Piwi::CreateWidget('CheckButtons', 'categories', 'vertical');
        $categories = $model->GetCategories();
        $canAddNewItem = false;
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $a) {
                if ($this->gadget->GetPermission('CategoryManage', $a['id'])) {
                    $catChecks->AddOption($a['name'], $a['id']);
                    $canAddNewItem = true;
                }
            }
        }
        $catDefault = explode(',', $this->gadget->registry->fetch('default_category'));
        $catChecks->SetDefault($catDefault);
        $catChecks->SetColumns(3);

        $tpl->SetVariable('category', _t('GLOBAL_CATEGORY'));
        $tpl->SetVariable('category_field', $catChecks->Get());

        // check dynamic ACL for access to at least one category
        if(!$canAddNewItem) {
            $menu  = $this->MenuBar('NewEntry');
            return $menu . _t('GLOBAL_ERROR_ACCESS_DENIED');
        }

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
        $editor->TextArea->SetRows(12);
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('96%');
        $tpl->SetVariable('editor', $editor->Get());

        // Allow Comments
        $allow = $this->gadget->registry->fetch('allow_comments') == 'true';
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption(_t('BLOG_ALLOW_COMMENTS'), 'comments', 'allow_comments', $allow);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', _t('GLOBAL_STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('BLOG_DRAFT'), '0');
        $statCombo->AddOption(_t('BLOG_PUBLISHED'), '1');
        if ($this->gadget->GetPermission('PublishEntries')) {
            $statCombo->SetDefault('1');
        } else {
            $statCombo->SetDefault('0');
            $statCombo->SetEnabled(false);
        }
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Save
        $tpl->SetVariable('missing_title', _t('BLOG_MISSING_TITLE'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->SetSubmit();
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
        $pubdate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $pubdate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
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

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('edit_entry/advanced/tags');
            $tpl->SetVariable('tags_label', _t('GLOBAL_TAGS'));
            $tags =& Piwi::CreateWidget('Entry', 'tags', '');
            $tags->SetStyle('width: 100%;');
            $tpl->SetVariable('tags', $tags->Get());
            $tpl->ParseBlock('edit_entry/advanced/tags');
        }

        if ($this->gadget->registry->fetch('trackback') == 'true') {
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
        $this->gadget->CheckPermission('AddEntries');
        $pModel = $this->gadget->loadAdminModel('Posts');
        $tModel = $this->gadget->loadAdminModel('Trackbacks');

        $names   = array('edit_timestamp:array', 'pubdate', 'categories:array', 'title',
                         'fasturl', 'allow_comments:array', 'published',
                         'trackback_to', 'meta_keywords', 'meta_desc', 'tags');
        $post    = jaws()->request->fetch($names, 'post');
        $content = jaws()->request->fetch(array('summary_block', 'text_block'), 'post', false);
        $post['trackback_to'] = str_replace("\r\n", "\n", $post['trackback_to']);

        $pubdate = null;
        if (isset($post['edit_timestamp']) && $post['edit_timestamp'][0] == 'yes') {
            $pubdate = $post['pubdate'];
        }

        foreach ($post['categories'] as $cat) {        
            if (!$this->gadget->GetPermission('CategoryManage', $cat)) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $id = $pModel->NewEntry($GLOBALS['app']->Session->GetAttribute('user') , $post['categories'],
                               $post['title'], $content['summary_block'], $content['text_block'],
                               $post['fasturl'], $post['meta_keywords'], $post['meta_desc'],
                               $post['tags'], isset($post['allow_comments'][0]), $post['trackback_to'],
                               $post['published'], $pubdate);

        if (!Jaws_Error::IsError($id)) {
            if ($this->gadget->registry->fetch('trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->gadget->urlMap('SingleView', array('id' => $id), true);
                $title = $post['title'];
                $text = $content['text_block'];
                if ($GLOBALS['app']->UTF8->strlen($text) > 250) {
                    $text = $GLOBALS['app']->UTF8->substr($text, 0, 250) . '...';
                }
                $tModel->SendTrackback($title, $text, $link, $to);
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
        $this->gadget->CheckPermission('AddEntries');
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
        $names   = array('id', 'action');
        $get     = jaws()->request->fetch($names, 'get');
        $names   = array('allow_comments', 'edit_advanced');
        $post    = jaws()->request->fetch($names, 'post');

        $id = !is_null($id) ? $id : $get['id'];
        $pModel = $this->gadget->loadModel('Posts');
        $cModel = $this->gadget->loadModel('Categories');
        $entry = $pModel->GetEntry($id);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Error::Fatal('Post not found', __FILE__, __LINE__);
        }

        if ($GLOBALS['app']->Session->GetAttribute('user') != $entry['user_id']) {
            $this->gadget->CheckPermission('ModifyOthersEntries');
        }

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadAdminTemplate('Entry.html');
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
        $categories = $cModel->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $a) {
                if ($this->gadget->GetPermission('CategoryManage', $a['id'])) {
                    $catChecks->AddOption($a['name'], $a['id']);
                }
            }
        }
        $catDefault = array();
        if (!Jaws_Error::isError($entry['categories'])) {
            foreach ($entry['categories'] as $cat) {
                if (!$this->gadget->GetPermission('CategoryManage', $cat['id'])) {
                    return Jaws_HTTPError::Get(403);
                }
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
        $editor->TextArea->SetRows(12);
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
        if (!$this->gadget->GetPermission('PublishEntries')) {
            $statCombo->SetEnabled(false);
        }
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Save
        $tpl->SetVariable('missing_title', _t('BLOG_MISSING_TITLE'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('BLOG_UPDATE'), STOCK_SAVE);
        $saveButton->SetSubmit();
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
        $pubdate->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $pubdate->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
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

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('edit_entry/advanced/tags');
            $tpl->SetVariable('tags_label', _t('GLOBAL_TAGS'));
            $postTags = implode(', ', $entry['tags']);
            $tags =& Piwi::CreateWidget('Entry', 'tags', $postTags);
            $tags->SetStyle('width: 100%;');
            $tpl->SetVariable('tags', $tags->Get());
            $tpl->ParseBlock('edit_entry/advanced/tags');
        }

        // Trackback
        if ($this->gadget->registry->fetch('trackback') == 'true') {
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
        return $this->EditEntry('preview', jaws()->request->fetch('id', 'post'));
    }

    /**
     * Save changes on an edited blog entry and shows the entries list on admin section
     *
     * @access  public
     */
    function SaveEditEntry()
    {
        $names   = array('id', 'edit_timestamp:array', 'pubdate', 'categories:array', 'title',
                         'fasturl', 'meta_keywords', 'meta_desc', 'tags',
                         'allow_comments:array', 'published', 'trackback_to');
        $post    = jaws()->request->fetch($names, 'post');
        $content = jaws()->request->fetch(array('summary_block', 'text_block'), 'post', false);

        $post['trackback_to'] = str_replace("\r\n", "\n", $post['trackback_to']);

        $pModel = $this->gadget->loadAdminModel('Posts');
        $tModel = $this->gadget->loadAdminModel('Trackbacks');
        $id = (int)$post['id'];

        $pubdate = null;
        if (isset($post['edit_timestamp']) && $post['edit_timestamp'][0] == 'yes') {
            $pubdate = $post['pubdate'];
        }

        $post['categories'] = !empty($post['categories'])? $post['categories'] : array();
        foreach ($post['categories'] as $cat) {
            if (!$this->gadget->GetPermission('CategoryManage', $cat)) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $pModel->UpdateEntry($id, $post['categories'], $post['title'], $content['summary_block'], $content['text_block'],
                            $post['fasturl'], $post['meta_keywords'], $post['meta_desc'], $post['tags'],
                            isset($post['allow_comments'][0]), $post['trackback_to'], $post['published'], $pubdate);
        if (!Jaws_Error::IsError($id)) {
            if ($this->gadget->registry->fetch('trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->gadget->urlMap('SingleView', array('id' => $id), true);
                $title = $post['title'];
                $text = $content['text_block'];
                if ($GLOBALS['app']->UTF8->strlen($text) > 250) {
                    $text = $GLOBALS['app']->UTF8->substr($text, 0, 250) . '...';
                }
                $tModel->SendTrackback($title, $text, $link, $to);
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
        $this->gadget->CheckPermission('DeleteEntries');
        $model = $this->gadget->loadAdminModel('Posts');
        $bModel = $this->gadget->loadModel('Posts');

        $post = jaws()->request->fetch(array('id', 'step'), 'post');
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

        $get = jaws()->request->fetch(array('id', 'action'), 'get');

        // Ask for confirmation...
        $entry = $bModel->GetEntry($get['id']);
        if (Jaws_Error::IsError($entry)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_DOES_NOT_EXISTS'));
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ListEntries');
        }

        $tpl = $this->gadget->loadAdminTemplate('EntryDelete.html');
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
        $tpl->SetVariable('text', $this->gadget->ParseText($entry['text']));
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
        $tpl = $this->gadget->loadAdminTemplate('Entries.html');
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

        $pModel = $this->gadget->loadAdminModel('Posts');
        $dpModel = $this->gadget->loadModel('DatePosts');
        $cModel = $this->gadget->loadModel('Categories');
        $monthentries = $dpModel->GetMonthsEntries();
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
        $categories = $cModel->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $cat) {
                if ($this->gadget->GetPermission('CategoryManage', $cat['id'])) {
                    $name = $cat['name'];
                    $catCombo->AddOption($name, $cat['id']);
                }
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
        $grid->TotalRows($pModel->TotalOfPosts());
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
        $actions->AddOption('&nbsp;', '');
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

        $model = $this->gadget->loadModel('Posts');
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

            if ($this->gadget->GetPermission('ManageComments')) {
                $link = Piwi::CreateWidget('Link', _t('BLOG_COMMENTS'),
                                           $common_url.'&amp;action=ManageComments&amp;filterby=postid&amp;filter='.$id,
                                           'images/stock/stock-comments.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('DeleteEntries')) {
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

}