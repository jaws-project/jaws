<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2020 Jaws Development Group
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
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Entry.html');
        $tpl->SetBlock('edit_entry');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('NewEntry'));

        $tpl->SetVariable('action', 'NewEntry');
        $tpl->SetVariable('id', 0);

        // title
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 750px');
        $titleEntry->setId('title');
        $tpl->SetVariable('title', Jaws::t('TITLE'));
        $tpl->SetVariable('title_field', $titleEntry->Get());

        // sub-title
        $subtitleEntry =& Piwi::CreateWidget('Entry', 'subtitle', '');
        $subtitleEntry->SetStyle('width: 750px');
        $subtitleEntry->setId('subtitle');
        $tpl->SetVariable('subtitle', _t('BLOG_SUBTITLE'));
        $tpl->SetVariable('subtitle_field', $subtitleEntry->Get());

        // Image
        $imageUrl = $this->app->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
        $blogImage =& Piwi::CreateWidget('Image', $imageUrl);
        $blogImage->SetID('blog_image');
        $tpl->SetVariable('blog_image', $blogImage->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'removeImage()');
        $tpl->SetVariable('btn_remove', $button->Get());

        $imageUrl = $this->app->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
        $blogImage =& Piwi::CreateWidget('Image', $imageUrl);
        $blogImage->SetID('blog_image');
        $tpl->SetVariable('blog_image', $blogImage->Get());

        $model = $this->gadget->model->load('Categories');
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

        $tpl->SetVariable('category', Jaws::t('CATEGORY'));
        $tpl->SetVariable('category_field', $catChecks->Get());

        // check dynamic ACL for access to at least one category
        if(!$canAddNewItem) {
            $menu  = $this->MenuBar('NewEntry');
            return $menu . Jaws::t('ERROR_ACCESS_DENIED');
        }

        // Summary
        $tpl->SetVariable('lbl_summary', _t('BLOG_ENTRY_SUMMARY'));
        $summary =& $this->app->loadEditor('Blog', 'summary_block', '', false);
        $summary->setId('summary_block');
        $summary->TextArea->SetRows(8);
        $summary->TextArea->SetStyle('width: 750px;');
        $tpl->SetVariable('summary', $summary->Get());

        // Body
        $tpl->SetVariable('text', _t('BLOG_ENTRY_BODY'));
        $editor =& $this->app->loadEditor('Blog', 'text_block', '', false);
        $editor->setId('text_block');
        $editor->TextArea->SetRows(12);
        $editor->TextArea->SetStyle('width: 100%;');
        $tpl->SetVariable('editor', $editor->Get());

        // Allow Comments
        $allow = $this->gadget->registry->fetch('allow_comments') == 'true';
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption(_t('BLOG_ALLOW_COMMENTS'), 'comments', 'allow_comments', $allow);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', Jaws::t('STATUS'));
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

        // Type
        $tpl->SetVariable('type', _t('BLOG_TYPE'));
        $cModel = Jaws_Gadget::getInstance('Categories')->model->load('Categories');
        $types = $cModel->GetCategories('Blog', 'Types');
        $typeCombo =& Piwi::CreateWidget('Combo', 'type');
        $typeCombo->setId('type');
        if (!Jaws_Error::IsError($types) && count($types) > 0) {
            foreach ($types as $type) {
                $typeCombo->AddOption($type['title'], $type['id']);
            }
        }
        $tpl->SetVariable('type_field', $typeCombo->Get());

        // Favorite
        $favorite =& Piwi::CreateWidget('CheckButtons', 'favorite');
        $favorite->AddOption(_t('BLOG_FAVORITE'), 'favorite', 'favorite');
        $tpl->SetVariable('favorite_field', $favorite->Get());

        // Save
        $tpl->SetVariable('missing_title', _t('BLOG_MISSING_TITLE'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $saveButton->SetSubmit();
        $tpl->SetVariable('save_button', $saveButton->Get());

        // Preview
        // TODO: We need a different stock icon for this.
        $previewButton =& Piwi::CreateWidget('Button', 'previewButton', Jaws::t('PREVIEW'), STOCK_PRINT_PREVIEW);
        $previewButton->AddEvent(ON_CLICK, "javascript:parseText(this.form);");

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
        $objDate = Jaws_Date::getInstance();
        $now = $objDate->Format(time(), 'Y-m-d H:i:s');
        $pubdate =& Piwi::CreateWidget('DatePicker', 'pubdate', $now);
        $pubdate->SetId('pubdate');
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

        $tpl->SetVariable('meta_keywords_label', Jaws::t('META_KEYWORDS'), '');
        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $metaKeywords->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $tpl->SetVariable('meta_desc_label', Jaws::t('META_DESCRIPTION'), '');
        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $metaDesc->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('edit_entry/advanced/tags');
            $tpl->SetVariable('tags_label', Jaws::t('TAGS'));
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
        $pModel = $this->gadget->model->loadAdmin('Posts');
        $tModel = $this->gadget->model->loadAdmin('Trackbacks');

        $names   = array('edit_timestamp:array', 'pubdate', 'categories:array', 'title', 'subtitle',
                         'fasturl', 'allow_comments:array', 'published', 'favorite:array', 'type',
                         'trackback_to', 'meta_keywords', 'meta_desc', 'tags');
        $post    = $this->gadget->request->fetch($names, 'post');
        $content = $this->gadget->request->fetch(array('summary_block', 'text_block'), 'post', 'strip_crlf');
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

        // Upload blog image
        $image = null;
        if (count($_FILES) > 0 && !empty($_FILES['image_file']['name'])) {
            $res = Jaws_FileManagement_File::uploadFiles(
                $_FILES,
                ROOT_DATA_PATH . 'blog/images/',
                'jpg,gif,png,jpeg,bmp',
                false
            );
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
            } elseif (empty($res)) {
                $this->gadget->session->push(Jaws::t('ERROR_UPLOAD_4'), RESPONSE_ERROR);
            } else {
                $image = $res['image_file'][0]['host_filename'];
            }
        }

        $id = $pModel->NewEntry($this->app->session->user->id , $post['categories'],
                           $post['title'], $post['subtitle'], $content['summary_block'], $content['text_block'],
                           $image, $post['fasturl'], $post['meta_keywords'], $post['meta_desc'],
                           $post['tags'], isset($post['allow_comments'][0]), $post['trackback_to'],
                           $post['published'], $post['type'], isset($post['favorite'][0]), $pubdate);

        if (!Jaws_Error::IsError($id)) {
            if ($this->gadget->registry->fetch('trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->gadget->urlMap('SingleView', array('id' => $id), true);
                $title = $post['title'];
                $text = $content['text_block'];
                if (Jaws_UTF8::strlen($text) > 250) {
                    $text = Jaws_UTF8::substr($text, 0, 250) . '...';
                }
                $tModel->SendTrackback($title, $text, $link, $to);
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=ListEntries');
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
        $get     = $this->gadget->request->fetch($names, 'get');
        $names   = array('allow_comments', 'edit_advanced');
        $post    = $this->gadget->request->fetch($names, 'post');

        $id = !is_null($id) ? $id : $get['id'];
        $pModel = $this->gadget->model->load('Posts');
        $cModel = $this->gadget->model->load('Categories');
        $entry = $pModel->GetEntry($id);
        if (Jaws_Error::IsError($entry)) {
            Jaws_Error::Fatal('Post not found', __FILE__, __LINE__);
        }

        if ($this->app->session->user->id != $entry['user_id']) {
            $this->gadget->CheckPermission('ModifyOthersEntries');
        }

        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Entry.html');
        $tpl->SetBlock('edit_entry');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        // Header
        $action = isset($get['action']) ? $get['action'] : null;
        $tpl->SetVariable('menubar', $this->MenuBar($action));
        $tpl->SetVariable('action', 'EditEntry');
        $tpl->SetVariable('id', $id);

        // Title
        $tpl->SetVariable('title', Jaws::t('TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', $entry['title']);
        $titleEntry->SetStyle('width: 750px');
        $tpl->SetVariable('title_field', $titleEntry->Get());

        // Sub-Title
        $tpl->SetVariable('subtitle', _t('BLOG_SUBTITLE'));
        $subtitleEntry =& Piwi::CreateWidget('Entry', 'subtitle', $entry['subtitle']);
        $subtitleEntry->SetStyle('width: 750px');
        $tpl->SetVariable('subtitle_field', $subtitleEntry->Get());

        // Image
        $imageUrl = $this->app->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
        if (!empty($entry['image'])) {
            $imageUrl = $this->app->getDataURL() . 'blog/images/'. $entry['image'];
        }
        $blogImage =& Piwi::CreateWidget('Image', $imageUrl);
        $blogImage->SetID('blog_image');
        $tpl->SetVariable('blog_image', $blogImage->Get());

        $imageFile =& Piwi::CreateWidget('FileEntry', 'image_file', '');
        $imageFile->SetID('image_file');
        $imageFile->SetSize(1);
        $imageFile->SetStyle('width:110px; padding:0;');
        $imageFile->AddEvent(ON_CHANGE, 'previewImage(this);');
        $tpl->SetVariable('upload_image', $imageFile->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_upload', '', STOCK_ADD);
        $tpl->SetVariable('btn_upload', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'removeImage()');
        $tpl->SetVariable('btn_remove', $button->Get());

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

        $tpl->SetVariable('category', Jaws::t('CATEGORY'));
        $tpl->SetVariable('category_field', $catChecks->Get());

        // for compatibility with old versions
        $more_pos = Jaws_UTF8::strpos($entry['text'], '[more]');
        if ($more_pos !== false) {
            $entry['summary'] = Jaws_UTF8::substr($entry['text'], 0, $more_pos);
            $entry['text']    = Jaws_UTF8::str_replace('[more]', '', $entry['text']);
        }

        // Summary
        $tpl->SetVariable('lbl_summary', _t('BLOG_ENTRY_SUMMARY'));
        $summary =& $this->app->loadEditor('Blog', 'summary_block', $entry['summary'], false);
        $summary->setId('summary_block');
        $summary->TextArea->SetRows(8);
        $summary->TextArea->SetStyle('width: 750px;');
        $tpl->SetVariable('summary', $summary->Get());

        // Body
        $tpl->SetVariable('text', _t('BLOG_BODY'));
        $editor =& $this->app->loadEditor('Blog', 'text_block', $entry['text'], false);
        $editor->setId('text_block');
        $editor->TextArea->SetRows(12);
        $editor->TextArea->SetStyle('width: 100%;');
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
        $tpl->SetVariable('status', Jaws::t('STATUS'));
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

        // Type
        $tpl->SetVariable('type', _t('BLOG_TYPE'));
        $cModel = Jaws_Gadget::getInstance('Categories')->model->load('Categories');
        $types = $cModel->GetCategories('Blog', 'Types');
        $typeCombo =& Piwi::CreateWidget('Combo', 'type');
        $typeCombo->setId('type');
        if (!Jaws_Error::IsError($types) && count($types) > 0) {
            foreach ($types as $type) {
                $typeCombo->AddOption($type['title'], $type['id']);
            }
        }
        $typeCombo->SetDefault(empty($entry['type'])? false : $entry['type']['id']);
        $tpl->SetVariable('type_field', $typeCombo->Get());

        // Favorite
        $favorite =& Piwi::CreateWidget('CheckButtons', 'favorite');
        $favorite->AddOption(_t('BLOG_FAVORITE'), 'favorite', 'favorite', $entry['favorite']);
        $tpl->SetVariable('favorite_field', $favorite->Get());

        // Save
        $tpl->SetVariable('missing_title', _t('BLOG_MISSING_TITLE'));
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('BLOG_UPDATE'), STOCK_SAVE);
        $saveButton->SetSubmit();
        $tpl->SetVariable('save_button', $saveButton->Get());

        // Preview
        // TODO: We need a different stock icon for this.
        $previewButton =& Piwi::CreateWidget('Button', 'preview',
                                                Jaws::t('PREVIEW'), STOCK_PRINT_PREVIEW);
        $previewButton->SetID('preview_button');
        $previewButton->AddEvent(ON_CLICK, "javascript:parseText(this.form);");
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

        $objDate = Jaws_Date::getInstance();
        $pubTime = $objDate->Format($entry['publishtime'], 'Y-m-d H:i:s');
        $pubdate =& Piwi::CreateWidget('DatePicker', 'pubdate', $pubTime);
        $pubdate->SetId('pubdate');
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

        $tpl->SetVariable('meta_keywords_label', Jaws::t('META_KEYWORDS'));
        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', $entry['meta_keywords']);
        $metaKeywords->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $tpl->SetVariable('meta_desc_label', Jaws::t('META_DESCRIPTION'));
        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', $entry['meta_description']);
        $metaDesc->SetStyle('width: 100%;');
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('edit_entry/advanced/tags');
            $tpl->SetVariable('tags_label', Jaws::t('TAGS'));
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
        return $this->EditEntry('preview', $this->gadget->request->fetch('id', 'post'));
    }

    /**
     * Save changes on an edited blog entry and shows the entries list on admin section
     *
     * @access  public
     */
    function SaveEditEntry()
    {
        $names   = array('id', 'edit_timestamp:array', 'pubdate', 'categories:array', 'title', 'subtitle',
                         'fasturl', 'meta_keywords', 'meta_desc', 'tags', 'deleteImage', 'favorite:array', 'type',
                         'allow_comments:array', 'published', 'trackback_to');
        $post    = $this->gadget->request->fetch($names, 'post');
        $content = $this->gadget->request->fetch(array('summary_block', 'text_block'), 'post', 'strip_crlf');

        $post['trackback_to'] = str_replace("\r\n", "\n", $post['trackback_to']);

        $pModel = $this->gadget->model->loadAdmin('Posts');
        $tModel = $this->gadget->model->loadAdmin('Trackbacks');
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

        // Upload blog image
        $image = false;
        if ($post['deleteImage'] == 'false') {
            $image = null;
            if (count($_FILES) > 0 && !empty($_FILES['image_file']['name'])) {
                $targetDir = ROOT_DATA_PATH . 'blog/images/';
                $res = Jaws_FileManagement_File::uploadFiles(
                    $_FILES,
                    $targetDir,
                    'jpg,gif,png,jpeg,bmp',
                    false
                );
                if (Jaws_Error::IsError($res)) {
                    $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
                } elseif (empty($res)) {
                    $this->gadget->session->push(Jaws::t('ERROR_UPLOAD_4'), RESPONSE_ERROR);
                } else {
                    $image = $res['image_file'][0]['host_filename'];

                    // Delete old image
                    $model = $this->gadget->model->load('Posts');
                    $blogEntry = $model->GetEntry($id);
                    if (!empty($blogEntry['image'])) {
                        Jaws_FileManagement_File::delete($targetDir . $blogEntry['image']);
                    }
                }
            }
        } else {
            // Delete old image
            $model = $this->gadget->model->load('Posts');
            $blogEntry = $model->GetEntry($id);
            if (!empty($blogEntry['image'])) {
                $targetDir = ROOT_DATA_PATH . 'blog/images/';
                Jaws_FileManagement_File::delete($targetDir . $blogEntry['image']);
            }
        }

        $pModel->UpdateEntry(
            $id, $post['categories'], $post['title'], $post['subtitle'], $content['summary_block'],
            $content['text_block'], $image, $post['fasturl'], $post['meta_keywords'], $post['meta_desc'],
            $post['tags'], isset($post['allow_comments'][0]), $post['trackback_to'], $post['published'],
            $post['type'], isset($post['favorite'][0]), $pubdate
        );
        if (!Jaws_Error::IsError($id)) {
            if ($this->gadget->registry->fetch('trackback') == 'true') {
                $to = explode("\n", $post['trackback_to']);
                $link = $this->gadget->urlMap('SingleView', array('id' => $id), true);
                $title = $post['title'];
                $text = $content['text_block'];
                if (Jaws_UTF8::strlen($text) > 250) {
                    $text = Jaws_UTF8::substr($text, 0, 250) . '...';
                }
                $tModel->SendTrackback($title, $text, $link, $to);
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=EditEntry&id=' . $id);
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
        $model = $this->gadget->model->loadAdmin('Posts');
        $bModel = $this->gadget->model->load('Posts');

        $post = $this->gadget->request->fetch(array('id', 'step'), 'post');
        if (!is_null($post['step']) && $post['step'] == 'delete') {
            // Delete Post
            $res = $model->DeleteEntry($post['id']);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push(_t('BLOG_ERROR_ENTRY_NOT_DELETED'), RESPONSE_ERROR);
            } else {
                $this->gadget->session->push(_t('BLOG_ENTRY_DELETED'), RESPONSE_NOTICE);
            }

            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=ListEntries');
        }

        $get = $this->gadget->request->fetch(array('id', 'action'), 'get');

        // Ask for confirmation...
        $entry = $bModel->GetEntry($get['id']);
        if (Jaws_Error::IsError($entry)) {
            $this->gadget->session->push(_t('BLOG_ERROR_DOES_NOT_EXISTS'));
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=ListEntries');
        }

        $tpl = $this->gadget->template->loadAdmin('EntryDelete.html');
        $tpl->SetBlock('delete_entry');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        // Header
        $tpl->SetVariable('menubar', $this->MenuBar($get['action']));
        // Message
        $tpl->SetVariable('delete_message', _t('BLOG_DELETE_CONFIRM_ENTRY'));
        // Delete
        $deleteButton =& Piwi::CreateWidget('Button', 'delete',
                                            Jaws::t('DELETE'), STOCK_DELETE);
        $deleteButton->SetSubmit();
        $tpl->SetVariable('delete_button', $deleteButton->Get());

        // Cancel
        $cancelButton =& Piwi::CreateWidget('Button', 'cancel',
                                            Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, "javascript:this.form.action.value = 'ListEntries'; this.form.submit(); ");
        $tpl->SetVariable('cancel_button', $cancelButton->Get());

        // ID
        $idHidden =& Piwi::CreateWidget('HiddenEntry', 'id', $get['id']);

        $tpl->SetVariable('id', $idHidden->Get());
        $tpl->SetVariable('title', $entry['title']);
        $tpl->SetVariable('text', $this->gadget->plugin->parse($entry['text']));
        $tpl->SetVariable('user', $entry['username']);
        $date = Jaws_Date::getInstance();
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
        $tpl = $this->gadget->template->loadAdmin('Entries.html');
        $tpl->SetBlock('list_entries');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $this->gadget->define('deleteConfirm', _t('BLOG_DELETE_MASSIVE_ENTRIES'));
        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('ListEntries'));

        // Filtering
        $pModel = $this->gadget->model->loadAdmin('Posts');
        $cModel = $this->gadget->model->load('Categories');

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
        $catCombo->AddEvent(ON_CHANGE, 'javascript:searchPost();');
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
        $statusCombo->AddEvent(ON_CHANGE, 'javascript:searchPost();');
        $tpl->SetVariable('status', Jaws::t('STATUS'));
        $tpl->SetVariable('status_field', $statusCombo->Get());

        $catCombo->SetDefault($category);
        $catCombo->AddEvent(ON_CHANGE, 'javascript:searchPost();');
        $tpl->SetVariable('category', _t('BLOG_CATEGORY'));
        $tpl->SetVariable('category_field', $catCombo->Get());

        // Free text search
        $searchButton =& Piwi::CreateWidget('Button', 'searchButton', Jaws::t('SEARCH'), STOCK_SEARCH);
        $searchButton->AddEvent(ON_CLICK, 'javascript:searchPost();');
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
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('BLOG_EDIT_TIMESTAMP')));
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('UPDATETIME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('BLOG_AUTHOR')));
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('ACTIONS')));

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('entries_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'entries_actions');
        $actions->SetID('entries_actions_combo');
        $actions->SetTitle(Jaws::t('ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(Jaws::t('DELETE'),  'delete');
        $actions->AddOption(_t('BLOG_DRAFT'),     '0');
        $actions->AddOption(_t('BLOG_PUBLISHED'), '1');

        $execute =& Piwi::CreateWidget('Button', 'executeEntryAction', '', STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript:entryDGAction(document.getElementById('entries_actions_combo'));");

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
     * @param   int     $cat        Category
     * @param   int     $status     Status (0=Draft, 1=Published)
     * @param   string  $search     Search word
     * @param   int     $limit      Limit data
     * @return  array   An array with all the data
     */
    function PostsData($cat, $status, $search, $limit = 0)
    {
        $common_url = BASE_SCRIPT . '?reqGadget=Blog';

        $model = $this->gadget->model->load('Posts');
        $entries = $model->AdvancedSearch($limit, $cat, $status, $search,
                                          $this->app->session->user->id);

        if (Jaws_Error::IsError($entries)) {
            return array();
        }

        $posts = array();
        $date = Jaws_Date::getInstance();

        foreach ($entries as $row) {
            $post = array();
            $id = $row['id'];
            $post['__KEY__'] = $id;
            $post['title'] = '<a href="'.$common_url.'&amp;reqAction=EditEntry&amp;id='.$id.'">'.
                $row['title'].'</a>';
            $post['publishtime'] = $date->Format($row['publishtime']);
            $post['updatetime']  = $date->Format($row['updatetime']);
            $post['username']    = $row['username'];
            $post['published']   = ($row['published'] === true) ? _t('BLOG_PUBLISHED') : _t('BLOG_DRAFT');

            $actions = '';
            $link = Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                                       $common_url.'&amp;reqAction=EditEntry&amp;id='.$id,
                                       STOCK_EDIT);
            $actions = $link->Get().'&nbsp;';

            if ($this->gadget->GetPermission('ManageComments')) {
                $link = Piwi::CreateWidget('Link', _t('BLOG_COMMENTS'),
                                           $common_url.'&amp;reqAction=ManageComments&amp;filterby=postid&amp;filter='.$id,
                                           'images/stock/stock-comments.png');
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('DeleteEntries')) {
                $link = Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                                           $common_url.'&amp;reqAction=DeleteEntry&amp;id='.$id,
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