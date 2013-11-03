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
class StaticPage_Actions_Admin_Default extends Jaws_Gadget_Action
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
                                'gadgets/StaticPage/Resources/images/groups.png');
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
     * Builds a pre-filled form
     *
     * @access  private
     * @param   string      $title          Page title
     * @param   string      $fast_url       Fast URL
     * @param   string      $meta_keys      Meta keywords
     * @param   string      $meta_desc      Meta description
     * @param   string      $tags           Tags (comma separated)
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
    function CreateForm($title, $fast_url, $meta_keys, $meta_desc, $tags, $content,
                        $published, $show_title, $language, $id, $gid, $action, $mode = 'base')
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadAdminTemplate('StaticPage.html');
        $tpl->SetBlock('pageform');

        $m_action  = jaws()->request->fetch('action', 'get');
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
        $vBox->SetStyle('display:inline;');

        $titleentry =& Piwi::CreateWidget('Entry', 'title', $title);
        $titleentry->SetTitle(_t('GLOBAL_TITLE'));
        $vBox->Add($titleentry);

        // Group
        if ($mode == 'base') {
            $model = $this->gadget->loadModel('Group');
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
            $show_title = ($show_title == true) ? 1 : 0;
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

        // Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tagsEntry =& Piwi::CreateWidget('Entry', 'tags', $tags);
            $tagsEntry->SetTitle(_t('GLOBAL_TAGS'));
            $advBox->Add($tagsEntry);
        }

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
        $editor->TextArea->SetStyle('width:100%;');
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
        $buttonbox->SetClass('actions');
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
}