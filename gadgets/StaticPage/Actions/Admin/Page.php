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
class StaticPage_Actions_Admin_Page extends StaticPage_Actions_Admin_Default
{
    /**
     * Builds datagrid structure
     *
     * @access  private
     * @return  string   XHTML datagrid
     */
    function DataGrid()
    {
        $model = $this->gadget->model->load();
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
        $pModel = $this->gadget->model->loadAdmin('Page');
        $tModel = $this->gadget->model->load('Translation');
        $pages = $pModel->SearchPages($group, $status, $search, $orderBy, $limit);
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
            $pageTranslation   = $tModel->GetTranslationsOfPage($page['page_id']);
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
    function ManagePages()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('StaticPage.html');
        $tpl->SetBlock('static_page');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $action = jaws()->request->fetch('action', 'get');
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
        $model = $this->gadget->model->load('Group');
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
     * Builds the form to create a new page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AddNewPage()
    {
        $this->gadget->CheckPermission('AddPage');
        return $this->CreateForm('', '', '', '', '', '', false, true, '', '', '', 'AddPage');
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
        $model = $this->gadget->model->loadAdmin('Page');

        $fetch = array('title', 'fast_url', 'meta_keys', 'meta_desc', 'tags',
                       'group_id', 'language', 'published', 'show_title');
        $post  = jaws()->request->fetch($fetch, 'post');
        $post['content'] = jaws()->request->fetch('content', 'post', false);

        $model->AddPage($post['title'], $post['group_id'], $post['show_title'], $post['content'], $post['language'],
            $post['fast_url'], $post['meta_keys'], $post['meta_desc'], $post['tags'], $post['published']);

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
        $model = $this->gadget->model->load('Page');

        $id = (int)jaws()->request->fetch('id', 'get');

        $page = $model->GetPage($id);
        if (Jaws_Error::IsError($page)) {
            $GLOBALS['app']->Session->PushLastResponse($page->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditPage&id=' . $id);
        }

        return $this->CreateForm($page['title'], $page['fast_url'], $page['meta_keywords'], $page['meta_description'],
            $page['tags'], $page['content'], $page['published'], $page['show_title'],  $page['language'],
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
        $model = $this->gadget->model->loadAdmin('Page');
        $fetch   = array('page', 'title', 'group_id', 'language', 'fast_url', 'meta_keys',
                         'meta_desc', 'tags', 'published', 'show_title');
        $post    = jaws()->request->fetch($fetch, 'post');
        $post['content'] = jaws()->request->fetch('content', 'post', false);
        $id      = (int)$post['page'];

        $model->UpdatePage($id, $post['group_id'], $post['show_title'], $post['title'],
            $post['content'], $post['language'], $post['fast_url'], $post['meta_keys'],
            $post['meta_desc'], $post['tags'], $post['published']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=StaticPage&action=EditPage&id=' . $id);
    }
}