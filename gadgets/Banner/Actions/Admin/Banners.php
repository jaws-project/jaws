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

        $tpl->SetVariable('legend_title', $this::t('BANNERS_ADD'));

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
        $tpl->SetVariable('lbl_bgroup', $this::t('GROUPS_GROUP'));

        // domains
        if ($this->gadget->registry->fetch('multi_domain', 'Users') == 'true') {
            $tpl->SetBlock('Banners/domain');
            $domains = Jaws_Gadget::getInstance('Users')->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                array_unshift($domains, array('id' =>  0, 'title' => _t('USERS_NODOMAIN')));
                array_unshift($domains, array('id' => -1, 'title' => _t('USERS_ALLDOMAIN')));
                $domainCombo =& Piwi::CreateWidget('Combo', 'domain_filter');
                foreach ($domains as $domain) {
                    $domainCombo->AddOption($domain['title'], $domain['id']);
                }
                $domainCombo->SetDefault(-1);
                $domainCombo->AddEvent(ON_CHANGE, "getBannersDataGrid('banners_datagrid', 0, true)");
                $tpl->SetVariable('domain_filter', $domainCombo->Get());
                $tpl->SetVariable('lbl_domain', _t('USERS_DOMAIN'));
            }
            $tpl->ParseBlock('Banners/domain');
        }

        $tpl->SetVariable('grid', $this->BannersDatagrid());
        $tpl->SetVariable('banner_ui', $this->BannerUI());
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript:saveBanner();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript:stopAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $this->gadget->define('incompleteBannerFields', $this::t('BANNERS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmBannerDelete',    $this::t('BANNERS_CONFIRM_DELETE'));
        $this->gadget->define('addBanner_title',        $this::t('BANNERS_ADD'));
        $this->gadget->define('editBanner_title',       $this::t('BANNERS_EDIT'));

        $this->gadget->define('textTemplate',  $text_banner);
        $this->gadget->define('imageTemplate', $image_banner);
        $this->gadget->define('flashTemplate', $flash_banner);

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

        // domains
        if ($this->gadget->registry->fetch('multi_domain', 'Users') == 'true') {
            $tpl->SetBlock('BannerInfo/domain');
            $domains = Jaws_Gadget::getInstance('Users')->model->load('Domains')->getDomains();
            if (!Jaws_Error::IsError($domains) && !empty($domains)) {
                array_unshift($domains, array('id' => 0, 'title' => _t('USERS_NODOMAIN')));
                $domainCombo =& Piwi::CreateWidget('Combo', 'domain');
                foreach ($domains as $domain) {
                    $domainCombo->AddOption($domain['title'], $domain['id']);
                }
                $domainCombo->SetDefault(0);
                $tpl->SetVariable('domain', $domainCombo->Get());
                $tpl->SetVariable('lbl_domain', _t('USERS_DOMAIN'));
            }
            $tpl->ParseBlock('BannerInfo/domain');
        }

        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $titleEntry->Get());

        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $tpl->SetVariable('url', $urlEntry->Get());

        $group_combo =& Piwi::CreateWidget('Combo', 'gid');
        $group_combo->SetID('gid');
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $group_combo->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('lbl_gid', $this::t('GROUPS_GROUPS'));
        $tpl->SetVariable('gid', $group_combo->Get());

        $check_upload =& Piwi::CreateWidget('CheckButtons', 'through_upload');
        $check_upload->AddEvent(ON_CLICK, 'javascript:changeThroughUpload(this.checked);');
        $check_upload->AddOption($this::t('BANNERS_THROUGH_UPLOADING'), '0');
        $tpl->SetVariable('th_upload', $check_upload->Get());

        $bannerEntry =& Piwi::CreateWidget('Entry', 'banner', '');
        $bannerEntry->SetID('banner');
        $tpl->SetVariable('lbl_banner', $this::t('BANNERS_BANNER'));
        $tpl->SetVariable('banner', $bannerEntry->Get());

        $upload_bannerEntry =& Piwi::CreateWidget('FileEntry', 'upload_banner', '');
        $upload_bannerEntry->SetID('upload_banner');
        $upload_bannerEntry->SetStyle('width: 256px; display: none;');
        $tpl->SetVariable('upload_banner', $upload_bannerEntry->Get());

        $template =& Piwi::CreateWidget('TextArea', 'template', '');
        $template->SetID('template');
        $template->SetRows(6);
        $tpl->SetVariable('lbl_template', $this::t('BANNERS_TEMPLATE'));
        $tpl->SetVariable('template', $template->Get());

        $btnText =& Piwi::CreateWidget('Button','btn_text', '', 'gadgets/Banner/Resources/images/text.png');
        $btnText->SetTitle($this::t('BANNERS_BANNERTYPE_TEXT'));
        $btnText->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.Banner.Defines.textTemplate);');
        $tpl->SetVariable('btn_text', $btnText->Get());

        $btnImage =& Piwi::CreateWidget('Button','btn_image', '', 'gadgets/Banner/Resources/images/image.png');
        $btnImage->SetTitle($this::t('BANNERS_BANNERTYPE_IMAGE'));
        $btnImage->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.Banner.Defines.imageTemplate);');
        $tpl->SetVariable('btn_image', $btnImage->Get());

        $btnFlash =& Piwi::CreateWidget('Button','btn_flash', '', 'gadgets/Banner/Resources/images/flash.png');
        $btnFlash->SetTitle($this::t('BANNERS_BANNERTYPE_FLASH'));
        $btnFlash->AddEvent(ON_CLICK, 'javascript:setTemplate(jaws.Banner.Defines.flashTemplate);');
        $tpl->SetVariable('btn_flash', $btnFlash->Get());

        $btnReset =& Piwi::CreateWidget('Button','btn_reset', '', STOCK_UNDO);
        $btnReset->AddEvent(ON_CLICK, 'javascript:setTemplate(defaultTemplate);');
        $tpl->SetVariable('btn_reset', $btnReset->Get());

        $tpl->SetVariable('lbl_limits', $this::t('BANNERS_LIMITATIONS'));
        $viewsLimitEntry =& Piwi::CreateWidget('Entry', 'views_limit', '');
        $viewsLimitEntry->SetID('views_limit');
        $viewsLimitEntry->setStyle('width: 78px;');
        $tpl->SetVariable('lbl_views_limit', $this::t('BANNERS_VIEWS'));
        $tpl->SetVariable('views_limit', $viewsLimitEntry->Get());

        $clicksLimitEntry =& Piwi::CreateWidget('Entry', 'clicks_limit', '');
        $clicksLimitEntry->SetID('clicks_limit');
        $clicksLimitEntry->setStyle('width: 78px;');
        $tpl->SetVariable('lbl_clicks_limit', $this::t('BANNERS_CLICKS'));
        $tpl->SetVariable('clicks_limit', $clicksLimitEntry->Get());

        $tpl->SetVariable('lbl_start_time', Jaws::t('START_TIME'));
        $tpl->SetBlock('BannerInfo/start_time');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'start_time'));
        $tpl->ParseBlock('BannerInfo/start_time');

        $tpl->SetVariable('lbl_stop_time', Jaws::t('STOP_TIME'));
        $tpl->SetBlock('BannerInfo/stop_time');
        $this->gadget->action->load('DatePicker')->calendar($tpl, array('name' => 'stop_time'));
        $tpl->ParseBlock('BannerInfo/stop_time');

        $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
        $randomType =& Piwi::CreateWidget('Combo', 'random');
        $randomType->SetID('random');
        $randomType->setStyle('width: 85px;');
        $randomType->AddOption(Jaws::t('NOO'),  '0');
        $randomType->AddOption(Jaws::t('YESS'), '1');
        $tpl->SetVariable('lbl_random', $this::t('BANNERS_RANDOM'));
        $tpl->SetVariable('random', $randomType->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->setStyle('width: 85px;');
        $published->AddOption(Jaws::t('NOO'),  0);
        $published->AddOption(Jaws::t('YESS'), 1);
        $published->SetDefault('1');
        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
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
    function GetBanners($gid, $domain = -1, $offset = null)
    {
        $model = $this->gadget->model->load('Banners');
        $banners = $model->GetBanners(-1, $gid, $domain, 18, $offset);
        if (Jaws_Error::IsError($banners)) {
            return array();
        }

        $newData = array();
        foreach($banners as $banner) {
            $bannerData = array();
            $bannerData['title'] = $banner['title'];
            $actions = '';
            if ($this->gadget->GetPermission('ManageBanners')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                    "javascript:editBanner(this, '".$banner['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
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
        $column1 = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
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

        $post = $this->gadget->request->fetch(
            array(
                'bid', 'domain', 'title', 'url', 'gid', 'type', 'banner',
                'views_limit', 'clicks_limit', 'start_time',
                'stop_time', 'random', 'published'
            ), 'post'
        );
        $post['template'] = $this->gadget->request->fetch('template', 'post', 'strip_crlf');

        $model = $this->gadget->model->loadAdmin('Banners');
        $res = Jaws_FileManagement_File::uploadFiles(
            $_FILES,
            ROOT_DATA_PATH . $this->gadget->DataDirectory,
            'jpg,gif,swf,png,jpeg,bmp,svg',
            false
        );
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        } elseif (empty($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_UPLOAD_4'), RESPONSE_ERROR);
        } else {
            if ($this->gadget->registry->fetch('multi_domain', 'Users') != 'true') {
                $post['domain'] = 0;
            }
            $filename = $res['upload_banner'][0]['host_filename'];
            if ($post['bid']!=0) {
                $model->UpdateBanner($post['bid'],
                    $post['domain'],
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
                $model->InsertBanner(
                    $post['domain'],
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
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Banner');
    }

}