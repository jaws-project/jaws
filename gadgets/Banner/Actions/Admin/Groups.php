<?php
/**
 * Banner Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Banner
 */
class Banner_Actions_Admin_Groups extends Banner_Actions_Admin_Default
{

    /**
     * Prepares the group management view
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('GroupBanners.html');
        $tpl->SetBlock('Groups');

        $addGroup =& Piwi::CreateWidget('Button', 'add_group', $this::t('GROUPS_ADD'), STOCK_NEW);
        $addGroup->AddEvent(ON_CLICK, "javascript:addGroup();");
        $tpl->SetVariable('add_group', $addGroup->Get());

        $saveGroup =& Piwi::CreateWidget('Button', 'save_group', Jaws::t('SAVE'), STOCK_SAVE);
        $saveGroup->AddEvent(ON_CLICK, "javascript:saveGroup();");
        $saveGroup->SetStyle('display: none;');
        $tpl->SetVariable('save_group', $saveGroup->Get());

        $GroupBanners =& Piwi::CreateWidget('Button', 'add_banners', $this::t('GROUPS_ADD_BANNERS'), STOCK_EDIT);
        $GroupBanners->AddEvent(ON_CLICK, "javascript:editGroupBanners();");
        $GroupBanners->SetStyle('display: none;');
        $tpl->SetVariable('add_banners', $GroupBanners->Get());

        $cancelAction =& Piwi::CreateWidget('Button', 'cancel_action', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancelAction->AddEvent(ON_CLICK, "javascript:stopAction();");
        $cancelAction->SetStyle('display: none;');
        $tpl->SetVariable('cancel', $cancelAction->Get());

        $deleteGroup =& Piwi::CreateWidget('Button', 'delete_group', $this::t('GROUPS_DELETE'), STOCK_DELETE);
        $deleteGroup->AddEvent(ON_CLICK, "javascript:deleteGroup();");
        $deleteGroup->SetStyle('display: none;');
        $tpl->SetVariable('delete_group', $deleteGroup->Get());

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));

        //Fill the groups combo..
        $comboGroups =& Piwi::CreateWidget('Combo', 'groups_combo');
        $comboGroups->SetID('groups_combo');
        $comboGroups->SetSize(20);
        $comboGroups->AddEvent(ON_CHANGE, 'javascript:editGroup(this.value);');

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups(-1);
        foreach($groups as $group) {
            $comboGroups->AddOption($group['title'], $group['id']);
        }
        $tpl->SetVariable('combo_groups', $comboGroups->Get());
        $this->gadget->define('incompleteGroupFields', $this::t('BANNERS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmGroupDelete', $this::t('GROUPS_CONFIRM_DELETE'));
        $tpl->ParseBlock('Groups');

        return $tpl->Get();
    }

    /**
     * Show a form to edit a given banner
     *
     * @access  public
     * @return  string XHTML template content
     */
    function EditGroupUI()
    {
        $tpl = $this->gadget->template->loadAdmin('GroupBanners.html');
        $tpl->SetBlock('GroupInfo');

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 300px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $tpl->SetVariable('lbl_count', $this::t('GROUPS_COUNT'));
        $countEntry =& Piwi::CreateWidget('Entry', 'count', '0');
        $countEntry->SetID('count');
        $countEntry->SetStyle('width: 120px;');
        $tpl->SetVariable('count', $countEntry->Get());

        $tpl->SetVariable('lbl_show_title', $this::t('GROUPS_SHOW_TITLE'));
        $showTitle =& Piwi::CreateWidget('Combo', 'show_title');
        $showTitle->SetStyle('width: 128px;');
        $showTitle->AddOption(Jaws::t('NOO'),  0);
        $showTitle->AddOption(Jaws::t('YESS'), 1);
        $showTitle->SetDefault('1');
        $tpl->SetVariable('show_title', $showTitle->Get());
        $tpl->SetVariable('lbl_show_title', $this::t('GROUPS_SHOW_TITLE'));

        $tpl->SetVariable('lbl_show_type', $this::t('GROUPS_SHOW_TYPE'));
        $showType =& Piwi::CreateWidget('Combo', 'show_type');
        $showType->SetStyle('width: 128px;');
        $showType->AddOption($this::t("GROUPS_SHOW_TYPE_1"),  1);
        $showType->AddOption($this::t("GROUPS_SHOW_TYPE_2"),  2);
        $showType->AddOption($this::t("GROUPS_SHOW_TYPE_3"),  3);
        $showType->SetDefault(1);
        $tpl->SetVariable('show_type', $showType->Get());

        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->SetStyle('width: 128px;');
        $published->AddOption(Jaws::t('NOO'),  0);
        $published->AddOption(Jaws::t('YESS'), 1);
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
        $tpl = $this->gadget->template->loadAdmin('GroupBanners.html');
        $tpl->SetBlock('GroupBanners');

        $model = $this->gadget->model->load('Banners');

        $tpl->SetVariable('lbl_banners', $this::t('GROUPS_MARK_BANNERS'));
        $bannersCombo =& Piwi::CreateWidget('Combo', 'banners_combo');
        $bannersCombo->SetID('banners_combo');
        $bannersCombo->SetStyle('width: 670px;');
        $banners = $model->GetBanners(-1, -1);
        foreach ($banners as $banner) {
            $bannersCombo->AddOption($banner['title'] . ' (' . $banner['url']. ')', $banner['id'], false);
        }
        $tpl->SetVariable('banners_combo', $bannersCombo->Get());

        $btnAdd =& Piwi::CreateWidget('Button', 'btn_add', '', STOCK_ADD);
        $btnAdd->AddEvent(ON_CLICK, "javascript:addBannerToList();");
        $tpl->SetVariable('btn_add', $btnAdd->Get());

        $tpl->SetVariable('lbl_list', $this::t('GROUPS_MEMBERS'));
        $bannersList =& Piwi::CreateWidget('Combo', 'group_members');
        $bannersList->SetID('group_members');
        $bannersList->SetSize('8');
        $bannersList->SetStyle('width: 670px;');
        $tpl->SetVariable('group_members', $bannersList->Get());

        $btnDel =& Piwi::CreateWidget('Button','btn_del', '', STOCK_CANCEL);
        $btnDel->AddEvent(ON_CLICK, 'javascript:delBannerFromList();');
        $tpl->SetVariable('btn_del', $btnDel->Get());

        $btnUp =& Piwi::CreateWidget('Button','btn_up', '', STOCK_UP);
        $btnUp->AddEvent(ON_CLICK, 'javascript:upBannerRank();');
        $tpl->SetVariable('btn_up', $btnUp->Get());

        $btnDown =& Piwi::CreateWidget('Button','btn_down', '', STOCK_DOWN);
        $btnDown->AddEvent(ON_CLICK, 'javascript:downBannerRank();');
        $tpl->SetVariable('btn_down', $btnDown->Get());

        $tpl->ParseBlock('GroupBanners');
        return $tpl->Get();
    }
}