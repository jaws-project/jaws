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
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('FASTURL')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('GROUP')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('PAGE_TRANSLATION')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('STATUS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('LAST_UPDATE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('ACTIONS')));

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
        $edit_url  = BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=EditPage&amp;id=';
        $add_turl  = BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=AddNewTranslation&amp;page=';
        $edit_turl = BASE_SCRIPT . '?reqGadget=StaticPage&amp;reqAction=EditTranslation&amp;id=';
        $date = Jaws_Date::getInstance();

        foreach ($pages as $page) {
            $pageData = array();
            $pageData['title'] = '<a href="'.$edit_url.$page['page_id'].'">'.$page['title'].'</a>';

            $param = array('pid' => !empty($page['fast_url']) ? $page['fast_url'] : $page['page_id']);
            $furl = $this->gadget->urlMap('Page', $param);
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
            $pageData['trans'].= '<small>[<a href="'.$add_turl.$page['page_id'].'">'.$this::t('ADD_LANGUAGE').'</a>]</small>';

            if ($page['published'] === true) {
                $pageData['published'] = $this::t('PUBLISHED');
            } else {
                $pageData['published'] = $this::t('DRAFT');
            }
            $pageData['date']  = $date->Format($page['updated'], 'Y-m-d H:i:s');
            $actions = '';
            if ($this->gadget->GetPermission('EditPage')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                    $edit_url.$page['page_id'],
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('DeletePage')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                    "javascript:deletePage('{$page['page_id']}');",
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
        $this->gadget->define('confirmPageDelete', Jaws::t('CONFIRM_DELETE', $this::t('PAGE')));
        $this->gadget->define('confirmMassiveDelete', $this::t('CONFIRM_MASIVE_DELETE_PAGE'));

        $tpl = $this->gadget->template->loadAdmin('StaticPage.html');
        $tpl->SetBlock('static_page');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $action = $this->gadget->request->fetch('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($action));

        $tpl->SetVariable('grid', $this->DataGrid());

        $toolBar   =& Piwi::CreateWidget('HBox');
        $deleteAll =& Piwi::CreateWidget('Button', 'deleteAllPages',
            Jaws::t('DELETE'),
            STOCK_DELETE);
        $deleteAll->AddEvent(ON_CLICK, "javascript:massiveDelete();");
        $toolBar->Add($deleteAll);

        $tpl->SetVariable('tools', $toolBar->Get());

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
        $tpl->SetVariable('language', $this::t('PAGE_LANGUAGE'));
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
        $tpl->SetVariable('group', $this::t('GROUP'));

        //Status filter
        $status = '';
        $statusCombo =& Piwi::CreateWidget('Combo', 'status');
        $statusCombo->setId('status');
        $statusCombo->AddOption('&nbsp;', '');
        $statusCombo->AddOption($this::t('PUBLISHED'), '1');
        $statusCombo->AddOption($this::t('DRAFT'), '0');
        $statusCombo->SetDefault($status);
        $statusCombo->AddEvent(ON_CHANGE, 'javascript:searchPage();');
        $tpl->SetVariable('status', $this::t('STATUS'));
        $tpl->SetVariable('status_field', $statusCombo->Get());

        //Order by filter
        $status = '';
        $orderCombo =& Piwi::CreateWidget('Combo', 'orderby');
        $orderCombo->setId('orderby');
        $orderCombo->AddOption(Jaws::t('CREATETIME'). ' &uarr;', 0);
        $orderCombo->AddOption(Jaws::t('CREATETIME'). ' &darr;', 1);
        $orderCombo->AddOption(Jaws::t('TITLE'). ' &uarr;',      2);
        $orderCombo->AddOption(Jaws::t('TITLE'). ' &darr;',      3);
        $orderCombo->AddOption(Jaws::t('UPDATETIME'). ' &uarr;', 4);
        $orderCombo->AddOption(Jaws::t('UPDATETIME'). ' &darr;', 5);
        $orderCombo->SetDefault(1);
        $orderCombo->AddEvent(ON_CHANGE, 'javascript:searchPage();');
        $tpl->SetVariable('orderby', $this::t('ORDERBY'));
        $tpl->SetVariable('orderby_field', $orderCombo->Get());

        // Free text search
        $searchButton =& Piwi::CreateWidget('Button', 'searchButton', Jaws::t('SEARCH'), STOCK_SEARCH);
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
        $post  = $this->gadget->request->fetch($fetch, 'post');
        $post['content'] = $this->gadget->request->fetch('content', 'post', 'strip_crlf');

        $model->AddPage($post['title'], $post['group_id'], $post['show_title'], $post['content'], $post['language'],
            $post['fast_url'], $post['meta_keys'], $post['meta_desc'], $post['tags'], $post['published']);

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage&reqAction=ManagePages');
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

        $id = (int)$this->gadget->request->fetch('id', 'get');

        $page = $model->GetPage($id);
        if (Jaws_Error::IsError($page)) {
            $this->gadget->session->push($page->GetMessage(), RESPONSE_ERROR);
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage&reqAction=EditPage&id=' . $id);
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
        $post    = $this->gadget->request->fetch($fetch, 'post');
        $post['content'] = $this->gadget->request->fetch('content', 'post', 'strip_crlf');
        $id      = (int)$post['page'];

        $model->UpdatePage($id, $post['group_id'], $post['show_title'], $post['title'],
            $post['content'], $post['language'], $post['fast_url'], $post['meta_keys'],
            $post['meta_desc'], $post['tags'], $post['published']);

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage&reqAction=EditPage&id=' . $id);
    }
}