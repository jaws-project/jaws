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
 * @copyright  2004-2021 Jaws Development Group
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
        $actions = array('ManagePages', 'AddNewPage','PreviewAddPage', 'Groups', 'Properties');
        if (!in_array($selected, $actions)) {
            $selected = 'ManagePages';
        }

        if ($selected == 'PreviewAddPage') {
            $selected = 'AddNewPage';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('ManagePages', $this::t('MENU_PAGES'),
                            BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=ManagePages', STOCK_DOCUMENTS);

        if ($this->gadget->GetPermission('AddPage')) {
            $menubar->AddOption('AddNewPage', $this::t('MENU_ADDPAGE'),
                                BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=AddNewPage', STOCK_NEW);
        }

        if ($this->gadget->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups', $this::t('GROUPS'),
                                BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=Groups',
                                'gadgets/StaticPage/Resources/images/groups.png');
        }

        if ($this->gadget->GetPermission('Properties')) {
            $menubar->AddOption('Properties', Jaws::t('SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=Properties',
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
        $this->gadget->define('confirmPageDelete', Jaws::t('CONFIRM_DELETE', $this::t('PAGE')));

        $tpl = $this->gadget->template->loadAdmin('StaticPage.html');
        $tpl->SetBlock('pageform');

        $m_action  = $this->gadget->request->fetch('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($m_action));

        $pageform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');

        $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'reqGadget', 'StaticPage'));
        $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'reqAction', $action));
        if ($action == 'SaveEditTranslation') {
            $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'trans_id', $id));
        } else {
            $pageform->Add(Piwi::CreateWidget('HiddenEntry', 'page', $id));
        }

        $vBox =& Piwi::CreateWidget('VBox');
        $vBox->SetId('page_options');
        $vBox->SetStyle('display:inline;');

        $titleentry =& Piwi::CreateWidget('Entry', 'title', $title);
        $titleentry->SetTitle(Jaws::t('TITLE'));
        $vBox->Add($titleentry);

        // Group
        if ($mode == 'base') {
            $model = $this->gadget->model->load('Group');
            $groups = $model->GetGroups();
            $combo =& Piwi::CreateWidget('Combo', 'group_id');
            foreach ($groups as $group) {
                if (!$this->gadget->GetPermission('AccessGroup', $group['id'])) {
                    continue;
                }
                $combo->AddOption($group['title'], $group['id']);
            }
            $combo->SetTitle($this::t('GROUP'));
            $combo->SetDefault($gid);
            $vBox->Add($combo);
        }

        // Status
        $published = ($published === true) ? 1 : 0;
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->AddOption($this::t('DRAFT'), '0');
        $statCombo->AddOption($this::t('PUBLISHED'), '1');
        $statCombo->SetDefault($published);
        $statCombo->setTitle($this::t('STATUS'));
        $vBox->Add($statCombo);

        if ($mode == 'base') {
            //show title
            $show_title = ($show_title == true) ? 1 : 0;
            $titleShowCombo =& Piwi::CreateWidget('Combo', 'show_title');
            $titleShowCombo->AddOption(Jaws::t('YESS'), '1');
            $titleShowCombo->AddOption(Jaws::t('NOO'),  '0');
            $titleShowCombo->SetDefault($show_title);
            $titleShowCombo->setTitle($this::t('SHOW_TITLE'));
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
        $languageCombo->setTitle($this::t('PAGE_LANGUAGE'));
        $vBox->Add($languageCombo);

        // Advanced Options
        $btnAdvanced =& Piwi::CreateWidget('Button', 'btn_advanced', $this::t('ADVANCED_OPTIONS'));
        $btnAdvanced->AddEvent(ON_CLICK, "javascript:$('#advanced_options').show(); $(this).hide();");

        $advBox =& Piwi::CreateWidget('VBox');
        $advBox->SetId('advanced_options');
        $advBox->SetStyle('display:none;');

        // Fast URL
        if ($mode == 'base') {
            $fasturlentry =& Piwi::CreateWidget('Entry', 'fast_url', $fast_url);
            $fasturlentry->SetTitle($this::t('FASTURL'));
            $fasturlentry->SetStyle('direction:ltr;');
            $advBox->Add($fasturlentry);
        }

        // Meta Keywords
        $metaKeysEntry =& Piwi::CreateWidget('Entry', 'meta_keys', $meta_keys);
        $metaKeysEntry->SetTitle(Jaws::t('META_KEYWORDS'));
        $advBox->Add($metaKeysEntry);

        // Meta Description
        $metaDescEntry =& Piwi::CreateWidget('Entry', 'meta_desc', $meta_desc);
        $metaDescEntry->SetTitle(Jaws::t('META_DESCRIPTION'));
        $advBox->Add($metaDescEntry);

        // Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tagsEntry =& Piwi::CreateWidget('Entry', 'tags', $tags);
            $tagsEntry->SetTitle(Jaws::t('TAGS'));
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
        $editor =& $this->app->loadEditor('StaticPage', 'content', $content, false);
        $editor->TextArea->SetRows(12);
        $editor->TextArea->SetStyle('width:100%;');
        $pageform->Add($editor);

        if ($mode == 'base') {
            if ($action == 'AddPage') {
                $submit =& Piwi::CreateWidget('Button', 'newpage', $this::t('ADD_PAGE'), STOCK_SAVE);
            } else {
                $submit =& Piwi::CreateWidget('Button', 'editpage', $this::t('UPDATE_PAGE'), STOCK_SAVE);
                $btnDelete =& Piwi::CreateWidget('Button', 'delpage', Jaws::t('DELETE'), STOCK_DELETE);
                $btnDelete->AddEvent(ON_CLICK, "javascript:deletePage($id, true);");
            }
        } else {
            if ($action == 'AddTranslation') {
                $submit =& Piwi::CreateWidget('Button', 'newpagetrans', $this::t('ADD_TRANSLATION'), STOCK_SAVE);
            } else {
                $submit =& Piwi::CreateWidget('Button', 'editpagetrans', $this::t('UPDATE_TRANSLATION'), STOCK_SAVE);
                $btnDelete =& Piwi::CreateWidget('Button', 'delpagetrans', Jaws::t('DELETE'), STOCK_DELETE);
                $btnDelete->AddEvent(ON_CLICK, "javascript:deleteTranslation($id, true);");
            }
        }

        $submit->SetSubmit();

        $cancel =& Piwi::CreateWidget('Button', 'cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        ///FIXME use the proper url stuff
        $cancel->AddEvent(ON_CLICK, "javascript:window.location = '".BASE_SCRIPT.'?reqGadget=StaticPage'."';");

        $preview =& Piwi::CreateWidget('Button', 'preview', Jaws::t('PREVIEW'), STOCK_PRINT_PREVIEW);
        $preview->AddEvent(ON_CLICK, 'javascript:parseText(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetClass('actions');
        $buttonbox->SetStyle(Jaws::t('LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($preview);
        $buttonbox->PackStart($cancel);
        if (isset($btnDelete) && $this->gadget->GetPermission('DeletePage')) {
            $buttonbox->PackStart($btnDelete);
        }
        $buttonbox->PackStart($submit);
        $pageform->Add($buttonbox);

        $tpl->setVariable('preview_header', Jaws::t('PREVIEW'));
        $tpl->SetVariable('form', $pageform->Get());

        $tpl->ParseBlock('pageform');
        return $tpl->Get();
    }
}