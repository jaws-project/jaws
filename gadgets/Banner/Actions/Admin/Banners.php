<?php
/**
 * Banner Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Banner
 */
class Banner_Actions_Admin_Banners extends Banner_Actions_Admin_Default
{
    /**
     * Show banners administration
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Banners()
    {
        $this->gadget->CheckPermission('ManageBanners');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Banners.html');
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

        $tpl = $this->gadget->template->loadAdmin('Banners.html');
        $tpl->SetBlock('Banners');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Banners'));

        $tpl->SetVariable('legend_title', _t('BANNER_BANNERS_ADD'));

        //Group filter
        $bGroup =& Piwi::CreateWidget('Combo', 'bgroup_filter');
        $bGroup->setStyle('min-width:150px;');
        $bGroup->AddEvent(ON_CHANGE, "getBannersDataGrid('banners_datagrid', 0, true)");
        $bGroup->AddOption('&nbsp;', -1);
        $model = $this->gadget->model->load('Groups');
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
        $btnSave->AddEvent(ON_CLICK, "javascript:saveBanner();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript:stopAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $this->gadget->layout->setVariable('incompleteBannerFields', _t('BANNER_BANNERS_INCOMPLETE_FIELDS'));
        $this->gadget->layout->setVariable('confirmBannerDelete',    _t('BANNER_BANNERS_CONFIRM_DELETE'));
        $this->gadget->layout->setVariable('addBanner_title',        _t('BANNER_BANNERS_ADD'));
        $this->gadget->layout->setVariable('editBanner_title',       _t('BANNER_BANNERS_EDIT'));

        $this->gadget->layout->setVariable('textTemplate',  $text_banner);
        $this->gadget->layout->setVariable('imageTemplate', $image_banner);
        $this->gadget->layout->setVariable('flashTemplate', $flash_banner);

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
        $tpl = $this->gadget->template->loadAdmin('Banners.html');
        $tpl->SetBlock('BannerInfo');

        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 344px;');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $titleEntry->Get());

        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlEntry->SetStyle('width: 344px;');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $urlEntry->Get());

        $group_combo =& Piwi::CreateWidget('Combo', 'gid');
        $group_combo->SetID('gid');
        $group_combo->setStyle('width: 352px;');
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $group_combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', _t('BANNER_GROUPS_GROUPS'));
        $tpl->SetVariable('gid', $group_combo->Get());

        $check_upload =& Piwi::CreateWidget('CheckButtons', 'through_upload');
        $check_upload->AddEvent(ON_CLICK, 'javascript:changeThroughUpload(this.checked);');
        $check_upload->AddOption(_t('BANNER_BANNERS_THROUGH_UPLOADING'), '0');
        $tpl->SetVariable('th_upload', $check_upload->Get());

        $bannerEntry =& Piwi::CreateWidget('Entry', 'banner', '');
        $bannerEntry->SetID('banner');
        $bannerEntry->SetStyle('width: 344px;');
        $tpl->SetVariable('lbl_banner', _t('BANNER_BANNERS_BANNER'));
        $tpl->SetVariable('banner', $bannerEntry->Get());

        $upload_bannerEntry =& Piwi::CreateWidget('FileEntry', 'upload_banner', '');
        $upload_bannerEntry->SetID('upload_banner');
        $upload_bannerEntry->SetStyle('width: 256px; display: none;');
        $tpl->SetVariable('upload_banner', $upload_bannerEntry->Get());

        $template =& Piwi::CreateWidget('TextArea', 'template', '');
        $template->SetID('template');
        $template->SetRows(6);
        $template->SetStyle('width: 310px;');
        $tpl->SetVariable('lbl_template', _t('BANNER_BANNERS_TEMPLATE'));
        $tpl->SetVariable('template', $template->Get());

        $btnText =& Piwi::CreateWidget('Button','btn_text', '', 'gadgets/Banner/Resources/images/text.png');
        $btnText->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_TEXT'));
        $btnText->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.gadgets.Banner.textTemplate);');
        $tpl->SetVariable('btn_text', $btnText->Get());

        $btnImage =& Piwi::CreateWidget('Button','btn_image', '', 'gadgets/Banner/Resources/images/image.png');
        $btnImage->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_IMAGE'));
        $btnImage->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.gadgets.Banner.imageTemplate);');
        $tpl->SetVariable('btn_image', $btnImage->Get());

        $btnFlash =& Piwi::CreateWidget('Button','btn_flash', '', 'gadgets/Banner/Resources/images/flash.png');
        $btnFlash->SetTitle(_t('BANNER_BANNERS_BANNERTYPE_FLASH'));
        $btnFlash->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.gadgets.Banner.flashTemplate);');
        $tpl->SetVariable('btn_flash', $btnFlash->Get());

        $btnReset =& Piwi::CreateWidget('Button','btn_reset', '', STOCK_UNDO);
        $btnReset->AddEvent(ON_CLICK, 'javascript:setTemplate(defaultTemplate);');
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
        $startTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $startTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
        $startTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $tpl->SetVariable('lbl_start_time', _t('GLOBAL_START_TIME'));
        $tpl->SetVariable('start_time', $startTime->Get());

        $stopTime =& Piwi::CreateWidget('DatePicker', 'stop_time', '');
        $stopTime->SetId('stop_time');
        $stopTime->showTimePicker(true);
        $stopTime->setDateFormat('%Y-%m-%d %H:%M:%S');
        $stopTime->SetIncludeCSS(false);
        $stopTime->SetIncludeJS(false);
        $stopTime->setLanguageCode($this->gadget->registry->fetch('admin_language', 'Settings'));
        $stopTime->setCalType($this->gadget->registry->fetch('calendar', 'Settings'));
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
     * Prepares the data (an array) of banners
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   int     $offset Offset of data
     * @return  array   Data
     */
    function GetBanners($gid, $offset = null)
    {
        $model = $this->gadget->model->load('Banners');
        $banners = $model->GetBanners(-1, $gid, 18, $offset);
        if (Jaws_Error::IsError($banners)) {
            return array();
        }

        $newData = array();
        foreach($banners as $banner) {
            $bannerData = array();
            $bannerData['title'] = $banner['title'];
            $actions = '';
            if ($this->gadget->GetPermission('ManageBanners')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript:editBanner(this, '".$banner['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript:deleteBanner(this, '".$banner['id']."');",
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
        $model = $this->gadget->model->load();
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
     * Insert and Update banners
     *
     * @access  public
     */
    function UploadBanner()
    {
        $this->gadget->CheckPermission('ManageBanners');

        $post = jaws()->request->fetch(array('bid', 'title', 'url', 'gid', 'type', 'banner',
            'views_limit', 'clicks_limit', 'start_time',
            'stop_time', 'random', 'published'), 'post');
        $post['template'] = jaws()->request->fetch('template', 'post', 'strip_crlf');

        $model = $this->gadget->model->loadAdmin('Banners');
        $res = Jaws_Utils::UploadFiles(
            $_FILES,
            JAWS_DATA . $this->gadget->DataDirectory,
            'jpg,gif,swf,png,jpeg,bmp,svg',
            false
        );
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } elseif (empty($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_UPLOAD_4'), RESPONSE_ERROR);
        } else {
            $filename = $res['upload_banner'][0]['host_filename'];
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
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Banner');
    }

}