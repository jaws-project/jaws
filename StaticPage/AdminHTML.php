<?php
/**
 * StaticPage Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string  $selected   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($selected)
    {
        $actions = array('Admin', 'AddNewPage','PreviewAddPage', 'Groups', 'Properties');
        if (!in_array($selected, $actions)) {
            $selected = 'Admin';
        }

        if ($selected == 'PreviewAddPage') {
            $selected = 'AddNewPage';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Admin', _t('STATICPAGE_MENU_PAGES'),
                            BASE_SCRIPT . '?gadget=StaticPage&amp;action=Admin', STOCK_DOCUMENTS);

        if ($this->gadget->GetPermission('AddPage')) {
            $menubar->AddOption('AddNewPage', _t('STATICPAGE_MENU_ADDPAGE'),
                                BASE_SCRIPT . '?gadget=StaticPage&amp;action=AddNewPage', STOCK_NEW);
        }

        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups', _t('STATICPAGE_GROUPS'),
                                BASE_SCRIPT . '?gadget=StaticPage&amp;action=Groups', 
                                'gadgets/StaticPage/images/groups.png');
        }

        if ($this->gadget->GetPermission('Properties')) {
            $menubar->AddOption('Properties', _t('GLOBAL_SETTINGS'),
                                BASE_SCRIPT . '?gadget=StaticPage&amp;action=Properties', 
                                'images/stock/properties.png');
        }
        $menubar->Activate($selected);

        return $menubar->Get();
    }

    /**
     * Builds the management UI for gadget properties
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->loadTemplate('StaticPage.html');
        $tpl->SetBlock('Properties');

        $request =& Jaws_Request::getInstance();
        $action  = $request->get('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($action));

        $model = $GLOBALS['app']->loadGadget('StaticPage', 'Model');

        //Build the form
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('GLOBAL_PROPERTIES'));

        //Default page (combo)
        $defaultPage =& Piwi::CreateWidget('Combo', 'default_page');
        $defaultPage->setTitle(_t('STATICPAGE_DEFAULT_PAGE'));
        $pages = $model->GetPages();
        if (Jaws_Error::isError($pages)) {
            $pages = array();
        }
        foreach($pages as $page) {
            $defaultPage->addOption($page['title'], $page['base_id']);
        }        
        $defaultPage->setDefault($this->gadget->registry->fetch('default_page'));
        $fieldset->add($defaultPage);

        // Use multilanguage pages?
        $multiLanguage =& Piwi::CreateWidget('Combo', 'multilanguage');
        $multiLanguage->setTitle(_t('STATICPAGE_USE_MULTILANGUAGE'));
        $multiLanguage->addOption(_t('GLOBAL_YES'), 'yes');
        $multiLanguage->addOption(_t('GLOBAL_NO'), 'no');           
        $multiLanguage->setDefault($this->gadget->registry->fetch('multilanguage'));
        $fieldset->add($multiLanguage);

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: updateSettings(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('Properties');

        return $tpl->Get();
    }

    /**
     * Builds datagrid structure
     *
     * @access  private
     * @return  string   XHTML datagrid
     */
    function DataGrid()
    {
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        $total = $model->TotalOfData('static_pages', 'page_id');

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->TotalRows($total);
        $grid->SetStyle('width: 100%;');
        $grid->SetID('pages_datagrid');
        $grid->useMultipleSelection();
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('STATICPAGE_FASTURL')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('STATICPAGE_GROUP')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('STATICPAGE_PAGE_TRANSLATION')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('STATICPAGE_STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('STATICPAGE_LAST_UPDATE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        return $grid->Get();
    }

    /**
     * Prepares data for pages grid
     *
     * @access  public
     * @param   int     $group      Group ID
     * @param   mixed   $status     Status of the page(s) (1/0 or Y/N)
     * @param   string  $search     Keywords(title/description) of the pages we are looking for
     * @param   int     $orderBy    Order by
     * @param   int     $limit      Data limit
     * @return  array   Grid data
     */
    function GetPages($group, $status, $search, $orderBy, $limit)
    {
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        $pages = $model->SearchPages($group, $status, $search, $orderBy, $limit);
        if (Jaws_Error::IsError($pages)) {
            return array();
        }

        $data = array();
        $edit_url  = BASE_SCRIPT . '?gadget=StaticPage&amp;action=EditPage&amp;id=';
        $add_turl  = BASE_SCRIPT . '?gadget=StaticPage&amp;action=AddNewTranslation&amp;page=';
        $edit_turl = BASE_SCRIPT . '?gadget=StaticPage&amp;action=EditTranslation&amp;id=';
        $date = $GLOBALS['app']->loadDate();

        foreach ($pages as $page) {
            $pageData = array();
            $pageData['title'] = '<a href="'.$edit_url.$page['page_id'].'">'.$page['title'].'</a>';

            $param = array('pid' => !empty($page['fast_url']) ? $page['fast_url'] : $page['page_id']);
            $furl = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Page', $param);
            $pageData['furl']  = '<a href="'.$furl.'">'.$page['fast_url'].'</a>';
            $pageData['group'] = $page['gtitle'];

            $pageData['trans'] = '';
            $pageTranslation   = $model->GetTranslationsOfPage($page['page_id']);
            if (is_array($pageTranslation)) {
                $transString = '';
                $counter     = 0;
                foreach($pageTranslation as $trans) {
                    if ($trans['language'] == $page['base_language']) {
                        continue;
                    }
                    $transString.= '<small><a href="'.$edit_turl.$trans['translation_id'].'">'.$trans['language'].'</a></small>, ';
                    if ($counter % 4 == 0 && $counter != 0) {
                        $transString.= '<br />';
                    }
                    $counter++;
                }
                $pageData['trans'].= substr($transString, 0, -2).'&nbsp;';
            }
            $pageData['trans'].= '<small>[<a href="'.$add_turl.$page['page_id'].'">'._t('STATICPAGE_ADD_LANGUAGE').'</a>]</small>';

            if ($page['published'] === true) {
                $pageData['published'] = _t('STATICPAGE_PUBLISHED');
            } else {
                $pageData['published'] = _t('STATICPAGE_DRAFT');
            }
            $pageData['date']  = $date->Format($page['updated'], 'Y-m-d H:i:s');
            $actions = '';
            if ($this->gadget->GetPermission('EditPage')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            $edit_url.$page['page_id'],
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('DeletePage')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deletePage('{$page['page_id']}');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $pageData['actions'] = $actions;
            $pageData['__KEY__'] = $page['page_id'];
            $data[] = $pageData;
        }
        return $data;
    }

    /**
     * Builds the gadget administration UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->loadTemplate('StaticPage.html');
        $tpl->SetBlock('static_page');
        
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $request =& Jaws_Request::getInstance();
        $action  = $request->get('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($action));

        $tpl->SetVariable('grid', $this->DataGrid());

        $toolBar   =& Piwi::CreateWidget('HBox');
        $deleteAll =& Piwi::CreateWidget('Button', 'deleteAllPages',
                                         _t('GLOBAL_DELETE'),
                                         STOCK_DELETE);
        $deleteAll->AddEvent(ON_CLICK, "javascript:massiveDelete();");
        $toolBar->Add($deleteAll);

        $tpl->SetVariable('tools', $toolBar->Get());
        $tpl->SetVariable('confirmPageDelete',    _t('GLOBAL_CONFIRM_DELETE', _t('STATICPAGE_PAGE')));
        $tpl->SetVariable('confirmMassiveDelete', _t('STATICPAGE_CONFIRM_MASIVE_DELETE_PAGE'));

        // Language filter
        $language = '';
        $languageCombo =& Piwi::CreateWidget('Combo', 'language');
        $languageCombo->setId('language');
        $languageCombo->AddOption('&nbsp;', '');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach($languages as $langCode => $langName) {
            $languageCombo->AddOption($langName, $langCode);
        }
        $languageCombo->SetDefault($language);
        $languageCombo->AddEvent(ON_CHANGE, 'javascript:searchPage();');
        $tpl->SetVariable('language', _t('STATICPAGE_PAGE_LANGUAGE'));
        $tpl->SetVariable('language_field', $languageCombo->Get());

        // Group filter
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'Model');
        $groups = $model->GetGroups();
        $combo =& Piwi::CreateWidget('Combo', 'group');
        $combo->AddOption('&nbsp;', 0);
        foreach ($groups as $group) {
            $combo->AddOption($group['title'], $group['id']);
        }
        $combo->AddEvent(ON_CHANGE, 'searchPage();');
        $tpl->SetVariable('group_field', $combo->Get());
        $tpl->SetVariable('group', _t('STATICPAGE_GROUP'));

        //Status filter
        $status = '';
        $statusCombo =& Piwi::CreateWidget('Combo', 'status');
        $statusCombo->setId('status');
        $statusCombo->AddOption('&nbsp;', '');
        $statusCombo->AddOption(_t('STATICPAGE_PUBLISHED'), '1');
        $statusCombo->AddOption(_t('STATICPAGE_DRAFT'), '0');
        $statusCombo->SetDefault($status);
        $statusCombo->AddEvent(ON_CHANGE, 'javascript:searchPage();');
        $tpl->SetVariable('status', _t('STATICPAGE_STATUS'));
        $tpl->SetVariable('status_field', $statusCombo->Get());

        //Order by filter
        $status = '';
        $orderCombo =& Piwi::CreateWidget('Combo', 'orderby');
        $orderCombo->setId('orderby');
        $orderCombo->AddOption(_t('GLOBAL_CREATETIME'). ' &uarr;', 0);
        $orderCombo->AddOption(_t('GLOBAL_CREATETIME'). ' &darr;', 1);
        $orderCombo->AddOption(_t('GLOBAL_TITLE'). ' &uarr;',      2);
        $orderCombo->AddOption(_t('GLOBAL_TITLE'). ' &darr;',      3);
        $orderCombo->AddOption(_t('GLOBAL_UPDATETIME'). ' &uarr;', 4);
        $orderCombo->AddOption(_t('GLOBAL_UPDATETIME'). ' &darr;', 5);
        $orderCombo->SetDefault(1);
        $orderCombo->AddEvent(ON_CHANGE, 'javascript:searchPage();');
        $tpl->SetVariable('orderby', _t('STATICPAGE_ORDERBY'));
        $tpl->SetVariable('orderby_field', $orderCombo->Get());

        // Free text search
        $searchButton =& Piwi::CreateWidget('Button', 'searchButton', _t('GLOBAL_SEARCH'), STOCK_SEARCH);
        $searchButton->AddEvent(ON_CLICK, 'javascript:searchPage();');
        $tpl->SetVariable('search', $searchButton->Get());

        $search = '';
        $searchEntry =& Piwi::CreateWidget('Entry', 'search', $search);
        $searchEntry->SetStyle('zwidth: 100%;');
        $tpl->SetVariable('search_field', $searchEntry->Get());
        
        $tpl->SetVariable('entries', $this->Datagrid());
        $tpl->ParseBlock('static_page');

        return $tpl->Get();
    }

    /**
     * Builds the form to create a new translation
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AddNewTranslation()
    {
        $this->gadget->CheckPermission('AddPage');

        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        //Get Id
        $request =& Jaws_Request::getInstance();
        $page_id = (int)$request->get('page', 'get');

        $page = $model->GetPage($page_id);
        if (Jaws_Error::IsError($page)) {
            $GLOBALS['app']->Session->PushLastResponse($page->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage');
        }
        return $this->CreateForm($page['title'], '', '', '', $page['content'], true, true, '', $page_id, '',
                                 'AddTranslation', 'translation');
    }

    /**
     * Builds the form to create a new page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AddNewPage()
    {
        $this->gadget->CheckPermission('AddPage');
        return $this->CreateForm('', '', '', '', '', false, true, '', '', '', 'AddPage');
    }

    /**
     * Adds a new page
     *
     * @access  public
     * @return  void
     */
    function AddPage()
    {
        $this->gadget->CheckPermission('AddPage');
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $fetch   = array('title', 'fast_url', 'meta_keys', 'meta_desc', 'group_id', 'language', 'published', 'show_title');
        $post    = $request->get($fetch, 'post');
        $post['content'] = $request->get('content', 'post', false);

        $result = $model->AddPage($post['title'], $post['group_id'], $post['show_title'], $post['content'], $post['language'],
                                  $post['fast_url'], $post['meta_keys'], $post['meta_desc'], $post['published']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=Admin');
    }

    /**
     * Builds the form to edit page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function EditPage()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $id      = (int)$request->get('id', 'get');

        $page = $model->GetPage($id);
        if (Jaws_Error::IsError($page)) {
            $GLOBALS['app']->Session->PushLastResponse($page->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditPage&id=' . $id);
        }

        return $this->CreateForm($page['title'], $page['fast_url'], $page['meta_keywords'], $page['meta_description'],
                                 $page['content'], $page['published'], $page['show_title'],  $page['language'],
                                 $id, $page['group_id'], 'SaveEditPage');
    }

    /**
     * Updates the page
     *
     * @access  public
     * @return  void
     */
    function SaveEditPage()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        $request =& Jaws_Request::getInstance();
        $fetch   = array('page', 'title', 'group_id', 'language', 'fast_url', 'meta_keys', 'meta_desc', 'published', 'show_title');
        $post    = $request->get($fetch, 'post');
        $post['content'] = $request->get('content', 'post', false);
        $id      = (int)$post['page'];

        $result = $model->UpdatePage($id, $post['group_id'], $post['show_title'], $post['title'], 
                                     $post['content'], $post['language'], $post['fast_url'], 
                                     $post['meta_keys'], $post['meta_desc'], $post['published']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditPage&id=' . $id);
    }

    /**
     * Adds a new page translation
     *
     * @access  public
     * @return  void
     */
    function AddTranslation()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        $request =& Jaws_Request::getInstance();
        $fetch   = array('page', 'title', 'content', 'language', 'meta_keys', 'meta_desc', 'published');
        $post    = $request->get($fetch, 'post');
        $post['content'] = $request->get('content', 'post', false);
        $page    = (int)$post['page'];
        
        $result = $model->AddTranslation($page, $post['title'], $post['content'], $post['language'],    
                                         $post['meta_keys'], $post['meta_desc'], $post['published']);
        if (Jaws_Error::isError($result)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage');
        } else {
            $translation = $model->GetPageTranslationByPage($page, $post['language']);
            if (Jaws_Error::isError($translation)) {
                Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage');
            } else {
                Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditTranslation&id=' . 
                                     $translation['translation_id']);
            }
        }
    }
    
    /**
     * Builds the form to edit a translation
     *
     * @access  public
     * @return  string  XHTML content
     */
    function EditTranslation()
    {
        $this->gadget->CheckPermission('AddPage');

        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        //Get Id
        $request  =& Jaws_Request::getInstance();
        $trans_id = (int)$request->get('id', 'get');
        
        $translation = $model->GetPageTranslation($trans_id);
        if (Jaws_Error::IsError($translation)) {
            $GLOBALS['app']->Session->PushLastResponse($translation->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage');
        }
        return $this->CreateForm($translation['title'], '', $translation['meta_keywords'], $translation['meta_description'],
                                 $translation['content'], $translation['published'], true, $translation['language'], $trans_id,
                                 '', 'SaveEditTranslation', 'translation');
    }

    /**
     * Updates a translation
     *
     * @access  public
     * @return  void
     */
    function SaveEditTranslation()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        $request =& Jaws_Request::getInstance();
        $fetch   = array('trans_id', 'title', 'language', 'meta_keys', 'meta_desc', 'published');
        $post    = $request->get($fetch, 'post');
        $post['content'] = $request->get('content', 'post', false);
        $trans   = (int)$post['trans_id'];
        $result = $model->UpdateTranslation($trans, $post['title'], $post['content'], $post['language'], 
                                            $post['meta_keys'], $post['meta_desc'], $post['published']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditTranslation&id=' . $trans);
    }

    /**
     * Builds a pre-filled form
     *
     * @access  private
     * @param   string      $title          Page title
     * @param   string      $fast_url       Fast URL
     * @param   string      $meta_keys      Meta keywords
     * @param   string      $meta_desc      Meta description
     * @param   string      $content        Page content
     * @param   bool        $published      Whether 'published' is checked or not
     * @param   bool        $show_title     Whether 'show_title' is checked or not
     * @param   string      $language       Page language
     * @param   int         $id             Page ID
     * @param   int         $gid            Group ID
     * @param   string      $action         The action to perform on page submit
     * @param   string      $mode           The mode we are using (base by default or translation)
     * @return  string      XHTML form
     */
    function CreateForm($title, $fast_url, $meta_keys, $meta_desc, $content,
                        $published, $show_title, $language, $id, $gid, $action, $mode = 'base')
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadTemplate('StaticPage.html');
        $tpl->SetBlock('pageform');

        $request =& Jaws_Request::getInstance();
        $m_action  = $request->get('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($m_action));

        $pageform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');

        $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'StaticPage'));
        $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'action', $action));
        if ($action == 'SaveEditTranslation') {
            $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'trans_id', $id));
        } else {
            $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'page', $id));
        }

        $vBox =& Piwi::CreateWidget('VBox');
        $vBox->SetId('page_options');
        $vBox->setSpacing(2);
        $vBox->SetStyle('display:inline;');

        $titleentry =& Piwi::CreateWidget('Entry', 'title', $title);
        $titleentry->SetTitle(_t('GLOBAL_TITLE'));
        $titleentry->SetStyle('width:300px');
        $vBox->Add($titleentry);

        // Group
        if ($mode == 'base') {
            $model = $GLOBALS['app']->LoadGadget('StaticPage', 'Model');
            $groups = $model->GetGroups();
            $combo =& Piwi::CreateWidget('Combo', 'group_id');
            foreach ($groups as $group) {
                $combo->AddOption($group['title'], $group['id']);
            }
            $combo->SetTitle(_t('STATICPAGE_GROUP'));
            $combo->SetDefault($gid);
            $vBox->Add($combo);
        }

        // Status
        $published = ($published === true) ? 1 : 0;
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->AddOption(_t('STATICPAGE_DRAFT'), '0');
        $statCombo->AddOption(_t('STATICPAGE_PUBLISHED'), '1');
        $statCombo->SetDefault($published);
        $statCombo->setTitle(_t('STATICPAGE_STATUS'));
        $vBox->Add($statCombo);

        if ($mode == 'base') {
            //show title
            $show_title = ($show_title === true) ? 1 : 0;
            $titleShowCombo =& Piwi::CreateWidget('Combo', 'show_title');
            $titleShowCombo->AddOption(_t('GLOBAL_YES'), '1');
            $titleShowCombo->AddOption(_t('GLOBAL_NO'),  '0');
            $titleShowCombo->SetDefault($show_title);
            $titleShowCombo->setTitle(_t('STATICPAGE_SHOW_TITLE'));
            $vBox->Add($titleShowCombo);
        }

        $language = (empty($language)) ? $this->gadget->registry->fetch('site_language', 'Settings') : $language;
        $languageCombo =& Piwi::CreateWidget('Combo', 'language');
        //Load the Settings AdminModel to get a list of all available languages
        $languages = Jaws_Utils::GetLanguagesList();
        foreach($languages as $langCode => $langName) {
            $languageCombo->AddOption($langName, $langCode);
        }
        $languageCombo->SetDefault($language);
        $languageCombo->setTitle(_t('STATICPAGE_PAGE_LANGUAGE'));
        $vBox->Add($languageCombo);
        
        // Advanced Options
        $btnAdvanced =& Piwi::CreateWidget('Button', 'btn_advanced', _t('STATICPAGE_ADVANCED_OPTIONS'));
        $btnAdvanced->AddEvent(ON_CLICK, 'javascript:$(\'advanced_options\').show(); this.hide();');

        $advBox =& Piwi::CreateWidget('VBox');
        $advBox->SetId('advanced_options');
        $advBox->setSpacing(2);
        $advBox->SetStyle('display:none;');

        // Fast URL
        if ($mode == 'base') {
            $fasturlentry =& Piwi::CreateWidget('Entry', 'fast_url', $fast_url);
            $fasturlentry->SetTitle(_t('STATICPAGE_FASTURL'));
            $fasturlentry->SetStyle('direction:ltr;');
            $advBox->Add($fasturlentry);
        }

        // Meta Keywords
        $metaKeysEntry =& Piwi::CreateWidget('Entry', 'meta_keys', $meta_keys);
        $metaKeysEntry->SetTitle(_t('GLOBAL_META_KEYWORDS'));
        $advBox->Add($metaKeysEntry);

        // Meta Description
        $metaDescEntry =& Piwi::CreateWidget('Entry', 'meta_desc', $meta_desc);
        $metaDescEntry->SetTitle(_t('GLOBAL_META_DESCRIPTION'));
        $advBox->Add($metaDescEntry);

        // Auto Draft
        if ($mode == 'base') {
            $autodraft = '<script type="text/javascript" language="javascript">setTimeout(\'startAutoDrafting();\', 1200000);</script>';
            $tpl->SetVariable('autodraft', $autodraft);
        }

        $pageform->Add($vBox);
        $pageform->Add($advBox);
        $pageform->Add($btnAdvanced);

        // Editor
        $editor =& $GLOBALS['app']->LoadEditor('StaticPage', 'content', $content, false);
        $editor->TextArea->SetRows(12);
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('96%');
        $pageform->Add($editor);

        if ($mode == 'base') {
            if ($action == 'AddPage') {
                $submit =& Piwi::CreateWidget('Button', 'newpage', _t('STATICPAGE_ADD_PAGE'), STOCK_SAVE);
            } else {
                $submit =& Piwi::CreateWidget('Button', 'editpage', _t('STATICPAGE_UPDATE_PAGE'), STOCK_SAVE);
                $btnDelete =& Piwi::CreateWidget('Button', 'delpage', _t('GLOBAL_DELETE'), STOCK_DELETE);
                $btnDelete->AddEvent(ON_CLICK, "javascript: deletePage($id, true);");
            }
        } else {
            if ($action == 'AddTranslation') {
                $submit =& Piwi::CreateWidget('Button', 'newpagetrans', _t('STATICPAGE_ADD_TRANSLATION'), STOCK_SAVE);
            } else {
                $submit =& Piwi::CreateWidget('Button', 'editpagetrans', _t('STATICPAGE_UPDATE_TRANSLATION'), STOCK_SAVE);
                $btnDelete =& Piwi::CreateWidget('Button', 'delpagetrans', _t('GLOBAL_DELETE'), STOCK_DELETE);
                $btnDelete->AddEvent(ON_CLICK, "javascript: deleteTranslation($id, true);");
            }
        }

        $submit->SetSubmit();

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        ///FIXME use the proper url stuff
        $cancel->AddEvent(ON_CLICK, "javascript: window.location = '".BASE_SCRIPT.'?gadget=StaticPage&amp;action=Admin'."';");

        $preview =& Piwi::CreateWidget('Button', 'preview', _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $preview->AddEvent(ON_CLICK, 'javascript: parseText(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($preview);
        $buttonbox->PackStart($cancel);
        if (isset($btnDelete) && $this->gadget->GetPermission('DeletePage')) {
            $buttonbox->PackStart($btnDelete);
        }
        $buttonbox->PackStart($submit);
        $pageform->Add($buttonbox);

        $tpl->setVariable('preview_header', _t('GLOBAL_PREVIEW'));
        $tpl->SetVariable('form', $pageform->Get());
        $tpl->SetVariable('confirmPageDelete', _t('GLOBAL_CONFIRM_DELETE', _t('STATICPAGE_PAGE')));

        $tpl->ParseBlock('pageform');
        return $tpl->Get();
    }

    /**
     * Builds the administration UI for groups
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->loadTemplate('Groups.html');
        $tpl->SetBlock('Groups');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));

        // Grid
        $tpl->SetVariable('grid', $this->GroupsDataGrid());

        $entry =& Piwi::CreateWidget('Entry', 'title', '');
        $entry->SetStyle('width: 200px;');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE').':');
        $tpl->SetVariable('title', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $entry->SetStyle('width:200px; direction:ltr;');
        $tpl->SetVariable('lbl_fast_url', _t('STATICPAGE_FASTURL').':');
        $tpl->SetVariable('fast_url', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_keys', '');
        $entry->SetStyle('width:200px;');
        $tpl->SetVariable('lbl_meta_keys', _t('GLOBAL_META_KEYWORDS').':');
        $tpl->SetVariable('meta_keys', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $entry->SetStyle('width:200px;');
        $tpl->SetVariable('lbl_meta_desc', _t('GLOBAL_META_DESCRIPTION').':');
        $tpl->SetVariable('meta_desc', $entry->Get());

        $combo =& Piwi::CreateWidget('Combo', 'visible');
        $combo->AddOption(_t('GLOBAL_NO'),  'false');
        $combo->AddOption(_t('GLOBAL_YES'), 'true');
        $combo->SetDefault('true');
        $tpl->SetVariable('visible', $combo->Get());
        $tpl->SetVariable('lbl_visible', _t('GLOBAL_VISIBLE').':');

        $btnSave =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript: saveGroup();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript: stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('legend_title',         _t('STATICPAGE_GROUP_ADD'));
        $tpl->SetVariable('add_group_title',      _t('STATICPAGE_GROUP_ADD'));
        $tpl->SetVariable('edit_group_title',     _t('STATICPAGE_GROUP_EDIT'));
        $tpl->SetVariable('confirm_group_delete', _t('STATICPAGE_GROUP_CONFIRM_DELETE'));
        $tpl->SetVariable('incomplete_fields',    _t('STATICPAGE_GROUP_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('Groups');
        return $tpl->Get();
    }

    /**
     * Builds the groups data grid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function GroupsDataGrid()
    {
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'AdminModel');
        //$total = $model->TotalOfData('poll_groups');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('groups_datagrid');
        //$grid->TotalRows(25);
        $grid->pageBy(10);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $column1->SetStyle('white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width:40px;');
        $grid->AddColumn($column2);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Prepares data for groups data grid
     *
     * @access  public
     * @param   int     $offset  Start offset of the result boundaries 
     * @return  array   Grid data
     */
    function GetGroupsGrid($offset)
    {
        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'Model');

        $groups = $model->GetGroups(null, 10, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }
        $result = array();
        foreach ($groups as $group) {
            $groupData = array();

            $groupData['title']  = ($group['visible'])? $group['title'] : '<font color="#aaa">'.$group['title'].'</font>';;

            $actions = '';
            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editGroup(this, '".$group['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteGroup(this, '".$group['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $groupData['actions'] = $actions;
            $result[] = $groupData;
        }

        return $result;
    }

}