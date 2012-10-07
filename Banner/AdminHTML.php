<?php
/**
 * Banner Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BannerAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Calls default admin action
     *
     * @access  public
     * @return  string  XTHML Template content
     */
    function Admin()
    {
        if ($this->GetPermission('ManageBanners')) {
            return $this->Banners();
        } elseif ($this->GetPermission('ManageGroups')) {
            return $this->Groups();
        }

        $this->CheckPermission('ViewReports');
    }

    /**
     * Prepares the banners menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML template of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Banners', 'Groups', 'Reports');
        if (!in_array($action, $actions)) {
            $action = 'Banners';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        if ($this->GetPermission('ManageBanners')) {
            $menubar->AddOption('Banners', _t('BANNER_NAME'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Admin', 'gadgets/Banner/images/banners_mini.png');
        }
        if ($this->GetPermission('ManageGroups')) {
            $menubar->AddOption('Groups', _t('BANNER_GROUPS_GROUPS'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Groups', 'gadgets/Banner/images/groups_mini.png');
        }
        if ($this->GetPermission('ViewReports')) {
            $menubar->AddOption('Reports', _t('BANNER_REPORTS_REPORTS'),
                                BASE_SCRIPT . '?gadget=Banner&amp;action=Reports', 'gadgets/Banner/images/reports_mini.png');
        }
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Prepares the data (an array) of banners
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   int     $offset Offset of data
     * @return  array   Data
     */
    function GetBanners($gid, $offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $banners = $model->GetBanners(-1, $gid, 18, $offset);
        if (Jaws_Error::IsError($banners)) {
            return array();
        }

        $newData = array();
        foreach($banners as $banner) {
            $bannerData = array();
            $bannerData['title'] = $banner['title'];
            $actions = '';
            if ($this->GetPermission('ManageBanners')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript: editBanner(this, '".$banner['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteBanner(this, '".$banner['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $bannerData['actions'] = $actions;
            $newData[] = $bannerData;
        }
        return $newData;
    }

    /**
     * Build the datagrid of banners
     *
     * @access  public
     * @return  string  XHTML template of Datagrid
     */
    function BannersDatagrid()
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $total = $model->TotalOfData('banners');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('banners_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(18);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 60px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Show banners administration
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Banners()
    {
        $this->CheckPermission('ManageBanners');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminBanners.html');
        $tpl->SetBlock('text_banner');
        $text_banner = $tpl->GetCurrentBlockContent();
        $text_banner = addslashes($text_banner);
        $text_banner = str_replace(chr(10).chr(13), "\\n\\r", $text_banner);
        $text_banner = str_replace(chr(13), "\\r", $text_banner);
        $text_banner = str_replace(chr(10), "\\n", $text_banner);
        $tpl->ParseBlock('text_banner');

        $tpl->SetBlock('image_banner');
        $image_banner = $tpl->GetCurrentBlockContent();
        $image_banner = addslashes($image_banner);
        $image_banner = str_replace(chr(10).chr(13), "\\n\\r", $image_banner);
        $image_banner = str_replace(chr(13), "\\r", $image_banner);
        $image_banner = str_replace(chr(10), "\\n", $image_banner);
        $tpl->ParseBlock('image_banner');

        $tpl->SetBlock('flash_banner');
        $flash_banner = $tpl->GetCurrentBlockContent();
        $flash_banner = addslashes($flash_banner);
        $flash_banner = str_replace(chr(10).chr(13), "\\n\\r", $flash_banner);
        $flash_banner = str_replace(chr(13), "\\r", $flash_banner);
        $flash_banner = str_replace(chr(10), "\\n", $flash_banner);
        $tpl->ParseBlock('flash_banner');

        $tpl->Load('AdminBanners.html');
        $tpl->SetBlock('Banners');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Banners'));
        
        //Group filter
        $bGroup =& Piwi::CreateWidget('Combo', 'bgroup_filter');
        $bGroup->setStyle('min-width:150px;');
        $bGroup->AddEvent(ON_CHANGE, "getBannersDataGrid('banners_datagrid', 0, true)");
        $bGroup->AddOption('', -1);
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $bGroup->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('bgroup_filter', $bGroup->Get());
        $tpl->SetVariable('lbl_bgroup', _t('BANNER_GROUPS_GROUP'));

        $tpl->SetVariable('grid', $this->BannersDatagrid());
        $tpl->SetVariable('banner_ui', $this->BannerUI());
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript: saveBanner();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript: stopAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('incompleteBannerFields', _t('BANNER_BANNERS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmBannerDelete',    _t('BANNER_BANNERS_CONFIRM_DELETE'));
        $tpl->SetVariable('legend_title',           _t('BANNER_BANNERS_ADD'));
        $tpl->SetVariable('addBanner_title',        _t('BANNER_BANNERS_ADD'));
        $tpl->SetVariable('editBanner_title',       _t('BANNER_BANNERS_EDIT'));

        $tpl->SetVariable('textTemplate',  $text_banner);
        $tpl->SetVariable('imageTemplate', $image_banner);
        $tpl->SetVariable('flashTemplate', $flash_banner);

        $tpl->ParseBlock('Banners');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given banner
     *
     * @access  public
     * @return  string XHTML template content
     */
    function BannerUI()
    {
        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminBanners.html');
        $tpl->SetBlock('BannerInfo');

        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $titleEntry->Get());

        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $urlEntry->Get());

        $group_combo =& Piwi::CreateWidget('Combo', 'gid');
        $group_combo->SetID('gid');
        $group_combo->setStyle('width: 262px;');
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $group_combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('BANNER_GROUPS_GROUPS'));
        $tpl->SetVariable('gid', $group_combo->Get());

        $check_upload =& Piwi::CreateWidget('CheckButtons', 'through_upload');
        $check_upload->AddEvent(ON_CLICK, 'javascript: changeThroughUpload(this.checked);');
        $check_upload->AddOption(_t('BANNER_BANNERS_THROUGH_UPLOADING'), '0');
        $tpl->SetVariable('th_upload', $check_upload->Get());

        $bannerEntry =& Piwi::CreateWidget('Entry', 'banner', '');
        $bannerEntry->SetID('banner');
        $bannerEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_banner', _t('BANNER_BANNERS_BANNER'));
        $tpl->SetVariable('banner', $bannerEntry->Get());

        $upload_bannerEntry =& Piwi::CreateWidget('FileEntry', 'upload_banner', '');
        $upload_bannerEntry->SetID('upload_banner');
        $upload_bannerEntry->SetStyle('width: 256px; display: none;');
        $tpl->SetVariable('upload_banner', $upload_bannerEntry->Get());
        
        $template =& Piwi::CreateWidget('TextArea', 'template', '');
        $template->SetID('template');
        $template->SetRows(6);
        $template->SetStyle('width: 256px;');
        $tpl->SetVariable('lbl_template', _t('BANNER_BANNERS_TEMPLATE'));
        $tpl->SetVariable('template', $template->Get());

        $btnText =& Piwi::CreateWidget('Button','btn_text', '', 'gadgets/Banner/images/text.png');
        $btnText->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_TEXT'));
        $btnText->AddEvent(ON_CLICK, 'javascript: setTemplate(textTemplate);');
        $tpl->SetVariable('btn_text', $btnText->Get());

        $btnImage =& Piwi::CreateWidget('Button','btn_image', '', 'gadgets/Banner/images/image.png');
        $btnImage->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_IMAGE'));
        $btnImage->AddEvent(ON_CLICK, 'javascript: setTemplate(imageTemplate);');
        $tpl->SetVariable('btn_image', $btnImage->Get());

        $btnFlash =& Piwi::CreateWidget('Button','btn_flash', '', 'gadgets/Banner/images/flash.png');
        $btnFlash->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_FLASH'));
        $btnFlash->AddEvent(ON_CLICK, 'javascript: setTemplate(flashTemplate);');
        $tpl->SetVariable('btn_flash', $btnFlash->Get());

        $btnReset =& Piwi::CreateWidget('Button','btn_reset', '', STOCK_UNDO);
        $btnReset->AddEvent(ON_CLICK, 'javascript: setTemplate(defaultTemplate);');
        $tpl->SetVariable('btn_reset', $btnReset->Get());

        $tpl->SetVariable('lbl_limits', _t('BANNER_BANNERS_LIMITATIONS'));
        $viewsLimitEntry =& Piwi::CreateWidget('Entry', 'views_limit', '');
        $viewsLimitEntry->SetID('views_limit');
        $viewsLimitEntry->setStyle('width: 78px;');
        $tpl->SetVariable('lbl_views_limit', _t('BANNER_BANNERS_VIEWS'));
        $tpl->SetVariable('views_limit', $viewsLimitEntry->Get());

        $clicksLimitEntry =& Piwi::CreateWidget('Entry', 'clicks_limit', '');
        $clicksLimitEntry->SetID('clicks_limit');
        $clicksLimitEntry->setStyle('width: 78px;');
        $tpl->SetVariable('lbl_clicks_limit', _t('BANNER_BANNERS_CLICKS'));
        $tpl->SetVariable('clicks_limit', $clicksLimitEntry->Get());

        $startTime =& Piwi::CreateWidget('DatePicker', 'start_time', '');
        $startTime->SetId('start_time');
        $startTime->showTimePicker(true);
        $startTime->setLanguageCode($GLOBALS['app']->Registry->Get('/config/calendar_language'));
        $startTime->setCalType($GLOBALS['app']->Registry->Get('/config/calendar_type'));
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->showTimePicker(true);
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->SetIncludeCSS(false);
        $stopTime->SetIncludeJS(false);
        $stopTime->setLanguageCode($GLOBALS['app']->Registry->Get('/config/calendar_language'));
        $stopTime->setCalType($GLOBALS['app']->Registry->Get('/config/calendar_type'));
        $tpl->SetVariable('lbl_stop_time', _t('GLOBAL_STOP_TIME'));
        $tpl->SetVariable('stop_time', $stopTime->Get());

        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $randomType =& Piwi::CreateWidget('Combo', 'random');
        $randomType->SetID('random');
        $randomType->setStyle('width: 85px;');
        $randomType->AddOption(_t('GLOBAL_NO'),  '0');
        $randomType->AddOption(_t('GLOBAL_YES'), '1');
        $tpl->SetVariable('lbl_random', _t('BANNER_BANNERS_RANDOM'));
        $tpl->SetVariable('random', $randomType->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->setStyle('width: 85px;');
        $published->AddOption(_t('GLOBAL_NO'),  0);
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->SetDefault('1');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('BannerInfo');
        return $tpl->Get();
    }

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Groups()
    {
        $this->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminGroupBanners.html');
        $tpl->SetBlock('Groups');

        $addGroup =& Piwi::CreateWidget('Button', 'add_group', _t('BANNER_GROUPS_ADD'), STOCK_NEW);
        $addGroup->AddEvent(ON_CLICK, "javascript: addGroup();");
        $tpl->SetVariable('add_group', $addGroup->Get());

        $saveGroup =& Piwi::CreateWidget('Button', 'save_group', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveGroup->AddEvent(ON_CLICK, "javascript: saveGroup();");
        $saveGroup->SetStyle('display: none;');
        $tpl->SetVariable('save_group', $saveGroup->Get());

        $GroupBanners =& Piwi::CreateWidget('Button', 'add_banners', _t('BANNER_GROUPS_ADD_BANNERS'), STOCK_EDIT);
        $GroupBanners->AddEvent(ON_CLICK, "javascript: editGroupBanners();");
        $GroupBanners->SetStyle('display: none;');
        $tpl->SetVariable('add_banners', $GroupBanners->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'cancel_action', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript: stopAction();");
        $cancelAction->SetStyle('display: none;');
        $tpl->SetVariable('cancel', $cancelAction->Get());

        $deleteGroup =& Piwi::CreateWidget('Button', 'delete_group', _t('BANNER_GROUPS_DELETE'), STOCK_DELETE);
        $deleteGroup->AddEvent(ON_CLICK, "javascript: deleteGroup();");
        $deleteGroup->SetStyle('display: none;');
        $tpl->SetVariable('delete_group', $deleteGroup->Get());

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));

        //Fill the groups combo..
        $comboGroups =& Piwi::CreateWidget('Combo', 'groups_combo');
        $comboGroups->SetSize(20);
        $comboGroups->SetStyle('width: 200px; height: 358px;');
        $comboGroups->AddEvent(ON_CHANGE, 'javascript: editGroup(this.value);');

        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $comboGroups->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('combo_groups', $comboGroups->Get());
        $tpl->SetVariable('incompleteGroupFields', _t('BANNER_BANNERS_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmGroupDelete', _t('BANNER_GROUPS_CONFIRM_DELETE'));
        $tpl->ParseBlock('Groups');

        return $tpl->Get();
    }

    /**
     * Insert and Update banners
     *
     * @access  public
     */
    function UploadBanner()
    {
        $this->CheckPermission('ManageBanners');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('bid', 'title', 'url', 'gid', 'type', 'banner',
                                    'views_limit', 'clicks_limit', 'start_time',
                                    'stop_time', 'random', 'published'), 'post');
        $post['template'] = $request->get('template', 'post', false);

        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $res = Jaws_Utils::UploadFiles($_FILES,
                                       JAWS_DATA . $model->GetBannersDirectory('/'),
                                       'jpg,gif,swf,png,jpeg,bmp,svg',
                                       '',
                                       false);
        if (!Jaws_Error::IsError($res)) {
            $filename = $res['upload_banner'][0];
            if ($post['bid']!=0) {
                $model->UpdateBanner($post['bid'],
                                     $post['title'],
                                     $post['url'],
                                     $post['gid'],
                                     $filename,
                                     $post['template'],
                                     $post['views_limit'],
                                     $post['clicks_limit'],
                                     $post['start_time'],
                                     $post['stop_time'],
                                     $post['random'],
                                     $post['published']);
            } else {
                $model->InsertBanner($post['title'],
                                     $post['url'],
                                     $post['gid'],
                                     $filename,
                                     $post['template'],
                                     $post['views_limit'],
                                     $post['clicks_limit'],
                                     $post['start_time'],
                                     $post['stop_time'],
                                     $post['random'],
                                     $post['published']);
            }
        } else {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Banner&action=Admin');
    }

    /**
     * Show a form to edit a given banner
     *
     * @access  public
     * @return  string XHTML template content
     */
    function EditGroupUI()
    {
        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminGroupBanners.html');
        $tpl->SetBlock('GroupInfo');

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 300px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $tpl->SetVariable('lbl_count', _t('BANNER_GROUPS_COUNT'));
        $countEntry =& Piwi::CreateWidget('Entry', 'count', '0');
        $countEntry->SetID('count');
        $countEntry->SetStyle('width: 120px;');
        $tpl->SetVariable('count', $countEntry->Get());

        $tpl->SetVariable('lbl_show_title', _t('BANNER_GROUPS_SHOW_TITLE'));
        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->SetStyle('width: 128px;');
        $showTitle->AddOption(_t('GLOBAL_NO'),  0);
        $showTitle->AddOption(_t('GLOBAL_YES'), 1);
        $showTitle->SetDefault('1');
        $tpl->SetVariable('show_title', $showTitle->Get());
        $tpl->SetVariable('lbl_show_title', _t('BANNER_GROUPS_SHOW_TITLE'));

        $tpl->SetVariable('lbl_show_type', _t('BANNER_GROUPS_SHOW_TYPE'));
        $showType =& Piwi::CreateWidget('Combo', 'show_type');
        $showType->SetStyle('width: 128px;');
        $showType->AddOption(_t("BANNER_GROUPS_SHOW_TYPE_0"),  0);
        $showType->AddOption(_t("BANNER_GROUPS_SHOW_TYPE_1"),  1);
        $showType->AddOption(_t("BANNER_GROUPS_SHOW_TYPE_2"),  2);
        $showType->SetDefault(0);
        $tpl->SetVariable('show_type', $showType->Get());

        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->SetStyle('width: 128px;');
        $published->AddOption(_t('GLOBAL_NO'),  0);
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->SetDefault(true);
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('GroupInfo');

        return $tpl->Get();
    }

    /**
     * Returns the banner-group management
     *
     * @access  public
     * @return  string    XHTML template content
     */
    function GetGroupBannersUI()
    {
        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminGroupBanners.html');
        $tpl->SetBlock('GroupBanners');

        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');

        $tpl->SetVariable('lbl_banners', _t('BANNER_GROUPS_MARK_BANNERS'));
        $bannersCombo =& Piwi::CreateWidget('Combo', 'banners_combo');
        $bannersCombo->SetID('banners_combo');
        $bannersCombo->SetStyle('width: 480px;');
        $banners = $model->GetBanners(-1, -1);
        foreach ($banners as $banner) {
            $bannersCombo->AddOption($banner['title'] . ' (' . $banner['url']. ')', $banner['id'], false);
        }
        $tpl->SetVariable('banners_combo', $bannersCombo->Get());

        $btnAdd =& Piwi::CreateWidget('Button', 'btn_add', '', STOCK_ADD);
        $btnAdd->AddEvent(ON_CLICK, "javascript: addBannerToList();");
        $tpl->SetVariable('btn_add', $btnAdd->Get());

        $tpl->SetVariable('lbl_list', _t('BANNER_GROUPS_MEMBERS'));
        $bannersList =& Piwi::CreateWidget('Combo', 'group_members');
        $bannersList->SetID('group_members');
        $bannersList->SetSize('8');
        $bannersList->SetStyle('width: 480px;');
        $tpl->SetVariable('group_members', $bannersList->Get());

        $btnDel =& Piwi::CreateWidget('Button','btn_del', '', STOCK_CANCEL);
        $btnDel->AddEvent(ON_CLICK, 'javascript: delBannerFromList();');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $btnUp =& Piwi::CreateWidget('Button','btn_up', '', STOCK_UP);
        $btnUp->AddEvent(ON_CLICK, 'javascript: upBannerRank();');
        $tpl->SetVariable('btn_up', $btnUp->Get());

        $btnDown =& Piwi::CreateWidget('Button','btn_down', '', STOCK_DOWN);
        $btnDown->AddEvent(ON_CLICK, 'javascript: downBannerRank();');
        $tpl->SetVariable('btn_down', $btnDown->Get());

        $tpl->ParseBlock('GroupBanners');
        return $tpl->Get();
    }

    /**
     * Get all the data
     *
     * @access  public
     * @param   int     $gid        group ID
     * @param   int     $offset
     * @return  array   Data array
     */
    function GetReportBanners($gid, $offset = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $banners = $model->GetBanners(-1, $gid, 18, $offset);
        if (Jaws_Error::IsError($banners)) {
            return array();
        }

        $new_banners = array();
        $objDate = $GLOBALS['app']->loadDate();
        foreach ($banners as $banner) {
            $item = array();
            $item['title']  = '<span><a href="'.$banner['url'].'" title="'.$banner['url'];
            $item['title'] .= '" target="_blank" style="text-decoration: none;">'.$banner['title'].'</a></span>';
            $item['views']  = $banner['views']. '/'.
                              ($banner['views_limitation']==0? '&#8734;' : $banner['views_limitation']);
            $item['clicks'] = $banner['clicks']. '/'.
                              ($banner['clicks_limitation']==0? '&#8734;' : $banner['clicks_limitation']);
            $item['start']  = '-';
            $item['stop']   = '-';
            if (!empty($banner['start_time'])) {
                $item['start'] = $objDate->Format($banner['start_time'], 'Y-m-d');
            }
            if (!empty($banner['stop_time'])) {
                $item['stop'] = $objDate->Format($banner['stop_time'], 'Y-m-d');
            }
            $item['status'] = (($banner['random']==1)?
                                _t('BANNER_REPORTS_BANNERS_STATUS_RANDOM'):
                                _t('BANNER_REPORTS_BANNERS_STATUS_ALWAYS')) . '/';
            $item['status'].= $banner['published']? _t('BANNER_REPORTS_BANNERS_STATUS_VISIBLE') :
                                                    _t('BANNER_REPORTS_BANNERS_STATUS_INVISIBLE');

            $actions = '';
            if ($this->GetPermission('ManageBanners')) {
                $link =& Piwi::CreateWidget('Link', _t('BANNER_BANNERS_RESET_VIEWS'),
                                            "javascript: resetViews('".$banner['id']."');",
                                            STOCK_REFRESH);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('BANNER_BANNERS_RESET_CLICKS'),
                                            "javascript: resetClicks('".$banner['id']."');",
                                            STOCK_RESET);
                $actions.= $link->Get().'&nbsp;';
            }
            $item['actions']= $actions;

            $new_banners[]  = $item;
        }
        return $new_banners;
    }

    /**
     * View report
     *
     * @access  public
     * @return  string     XHTML template content
     */
    function Reports()
    {
        $this->CheckPermission('ViewReports');
        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/Banner/templates/');
        $tpl->Load('AdminBannerReports.html');
        $tpl->SetBlock('Reports');

        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $total = $model->TotalOfData('banners', 'id');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(18);
        $datagrid->SetID('reports_datagrid');

        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('BANNER_BANNERS_VIEWS'), null, false);
        $column2->SetStyle('width: 64px; white-space:nowrap;');
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('BANNER_BANNERS_CLICKS'), null, false);
        $column3->SetStyle('width: 64px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_START_TIME'), null, false);
        $column4->SetStyle('width: 80px; white-space:nowrap;');
        $datagrid->AddColumn($column4);
        $column5 = Piwi::CreateWidget('Column', _t('GLOBAL_STOP_TIME'), null, false);
        $column5->SetStyle('width: 80px; white-space:nowrap;');
        $datagrid->AddColumn($column5);
        $column6 = Piwi::CreateWidget('Column', _t('GLOBAL_STATUS'), null, false);
        $column6->SetStyle('width: 120px; white-space:nowrap;');
        $datagrid->AddColumn($column6);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');
        $column7 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column7->SetStyle('width: 60px; white-space:nowrap;');
        $datagrid->AddColumn($column7);

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));
        $tpl->SetVariable('datagrid', $datagrid->Get());

        //Group filter
        $bGroup =& Piwi::CreateWidget('Combo', 'bgroup_filter');
        $bGroup->setStyle('min-width:200px;');
        $bGroup->AddEvent(ON_CHANGE, "getBannersDataGrid('reports_datagrid', 0, true)");
        $bGroup->AddOption('', -1);
        $model = $GLOBALS['app']->LoadGadget('Banner', 'AdminModel');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $bGroup->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('bgroup_filter', $bGroup->Get());
        $tpl->SetVariable('lbl_bgroup', _t('BANNER_GROUPS_GROUP'));

        $tpl->SetVariable('confirmResetBannerViews',  _t('BANNER_BANNERS_CONFIRM_RESET_VIEWS'));
        $tpl->SetVariable('confirmResetBannerClicks', _t('BANNER_BANNERS_CONFIRM_RESET_CLICKS'));

        $tpl->ParseBlock('Reports');
        return $tpl->Get();
    }
}