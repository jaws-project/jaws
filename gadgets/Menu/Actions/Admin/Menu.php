<?php
/**
 * Menu Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Menu
 */
class Menu_Actions_Admin_Menu extends Jaws_Gadget_Action
{
    /**
     * Builds Menu administration UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Menu()
    {
        $this->AjaxMe('script.js');
        $this->gadget->export('addMenuTitle', $this::t('ADD_MENU'));
        $this->gadget->export('editMenuTitle', $this::t('EDIT_MENU'));
        $this->gadget->export('addGroupTitle', $this::t('ADD_GROUP'));
        $this->gadget->export('editGroupTitle', $this::t('EDIT_GROUP'));
        $this->gadget->export('incompleteFields', $this::t('INCOMPLETE_FIELDS'));
        $this->gadget->export('confirmGroupDelete', $this::t('CONFIRM_DELETE_GROUP'));
        $this->gadget->export('confirmMenuDelete', $this::t('CONFIRM_DELETE_MENU'));
        $this->gadget->export('base_script', BASE_SCRIPT);

        $tpl = $this->gadget->template->loadAdmin('Menu.html');
        $tpl->SetBlock('menus');
        $tpl->SetBlock('menus/menus_base');

        $tpl->SetVariable('menus_trees', $this->GetMenusTrees());
        $add_btn =& Piwi::CreateWidget('Button','btn_add', $this::t('ADD_GROUP'), STOCK_NEW);
        $add_btn->AddEvent(ON_CLICK, 'javascript:addGroup();');
        $tpl->SetVariable('add', $add_btn->Get());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript:saveMenus();');
        $tpl->SetVariable('save', $save_btn->Get());

        $del_btn =& Piwi::CreateWidget('Button','btn_del', Jaws::t('DELETE'), STOCK_DELETE);
        $del_btn->SetStyle('display: none;');
        $del_btn->AddEvent(ON_CLICK, 'javascript:delMenus();');
        $tpl->SetVariable('del', $del_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $tpl->SetVariable('menu_tree_image', 'gadgets/Menu/Resources/images/menu-item.png');
        $tpl->SetVariable('menu_tree_title', $this::t('TREE_TITLE'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->ParseBlock('menus/menus_base');
        $tpl->ParseBlock('menus');
        return $tpl->Get();
    }

    /**
     * Retrieves Menu Level
     *
     * @access  public
     * @param   object  $model      Jaws_Model reference
     * @param   string  $tpl_str    XHTML template content passed by reference
     * @param   int     $gid        Group ID
     * @param   int     $pid
     * @return  string  XHTML template content
     */
    function GetMenuLevel(&$model, &$tpl_str, $gid, $pid)
    {
        $menus = $model->GetLevelsMenus($pid, $gid);
        if (Jaws_Error::IsError($menus) || empty($menus)) {
            return '';
        }

        $tpl = new Jaws_Template();
        $tpl->LoadFromString($tpl_str);
        $tpl->SetBlock('parent');
        foreach ($menus as $menu) {
            $tpl->SetBlock('parent/menu');
            $tpl->SetVariable('class_name', 'menu_levels');
            $tpl->SetVariable('mg_id', 'menu_'.$menu['id']);
            $tpl->SetVariable('icon', 'gadgets/Menu/Resources/images/menu-item.png');
            $tpl->SetVariable('title', $menu['title']);
            $tpl->SetVariable('js_edit_func', "editMenu({$menu['id']})");
            $tpl->SetVariable('add_title', $this::t('ADD_MENU'));
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->SetVariable('js_add_func', "addMenu($gid, {$menu['id']})");
            $tpl->SetVariable('sub_menus', $this->GetMenuLevel($model, $tpl_str, $gid, $menu['id']));
            $tpl->ParseBlock('parent/menu');
        }
        $tpl->ParseBlock('parent');
        return $tpl->Get();
    }

    /**
     * Providing a treeview of menus and gadgtes
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function GetMenusTrees()
    {
        $tpl = $this->gadget->template->loadAdmin('Menu.html');
        $tpl->SetBlock('menus');

        $mModel = $this->gadget->model->load('Menu');
        $gModel = $this->gadget->model->load('Group');
        $groups = $gModel->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('menus/menus_tree');
            $tpl_str = '<!-- BEGIN parent --><!-- BEGIN menu -->'.$tpl->GetCurrentBlockContent().'<!-- END menu --><!-- END parent -->';
            $tpl->SetVariable('class_name', 'menu_groups');
            $tpl->SetVariable('mg_id', 'group_'.$group['id']);
            $tpl->SetVariable('icon', 'gadgets/Menu/Resources/images/menu-group.png');
            $tpl->SetVariable('title', $group['title']);
            $tpl->SetVariable('js_edit_func', "editGroup({$group['id']})");
            $tpl->SetVariable('add_title', $this::t('ADD_MENU'));
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->SetVariable('js_add_func', "addMenu({$group['id']}, 0)");
            $tpl->SetVariable('sub_menus',  $this->GetMenuLevel($mModel, $tpl_str, $group['id'], 0));
            $tpl->ParseBlock('menus/menus_tree');
        }

        $tpl->ParseBlock('menus');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetGroupUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Menu.html');
        $tpl->SetBlock('menus');
        $tpl->SetBlock('menus/GroupsUI');

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 300px; margin-top:2px; margin-bottom:5px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        // home
        $groups = $this->gadget->model->load('Group')->GetGroups();
        $groups = array_column($groups, 'title', 'id');
        $menus = $this->gadget->model->load('Menu')->GetLevelsMenus(0, null, true);
        array_unshift($menus, array('id' => 0, 'gid' => 0, 'title' => '/'));

        $homeCombo =& Piwi::CreateWidget('Combo', 'home');
        $homeCombo->SetID('home');
        $homeCombo->setStyle('width: 256px;');
        foreach ($menus as $menu) {
            if (empty($menu['gid'])) {
                $homeCombo->AddOption($menu['title'], $menu['id']);
            } else {
                $homeCombo->AddOption($groups[$menu['gid']]. ' / '. $menu['title'], $menu['id']);
            }
        }
        $tpl->SetVariable('lbl_home', $this::t('HOME'));
        $tpl->SetVariable('home', $homeCombo->Get());

        // title view
        $titleview =& Piwi::CreateWidget('Combo', 'title_view');
        $titleview->SetID('title_view');
        $titleview->setStyle('width: 96px; margin-top:2px; margin-bottom:5px;');
        $titleview->AddOption(Jaws::t('NOO'),  '0');
        $titleview->AddOption(Jaws::t('YESS'), '1');
        $tpl->SetVariable('lbl_title_view', $this::t('GROUPS_TITLE_VIEW'));
        $tpl->SetVariable('title_view', $titleview->Get());

        $viewType =& Piwi::CreateWidget('Combo', 'view_type');
        $viewType->SetID('view_type');
        $viewType->setStyle('width: 96px; margin-top:2px; margin-bottom:5px;');
        $viewType->AddOption($this::t('GROUPS_VIEW_TYPE_1'), 1);
        $viewType->AddOption($this::t('GROUPS_VIEW_TYPE_2'), 2);
        $viewType->AddOption($this::t('GROUPS_VIEW_TYPE_3'), 3);
        $viewType->AddOption($this::t('GROUPS_VIEW_TYPE_4'), 4);
        $tpl->SetVariable('lbl_view_type', $this::t('GROUPS_VIEW_TYPE'));
        $tpl->SetVariable('view_type', $viewType->Get());

        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->SetStyle('width: 96px; margin-top:2px; margin-bottom:5px;');
        $published->AddOption(Jaws::t('NOO'),  0);
        $published->AddOption(Jaws::t('YESS'), 1);
        $published->SetDefault(1);
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('menus/GroupsUI');
        $tpl->ParseBlock('menus');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetMenuUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Menu.html');
        $tpl->SetBlock('menus');
        $tpl->SetBlock('menus/MenusUI');

        $model = $this->gadget->model->load('Group');
        $groups = $model->GetGroups();
        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $groupCombo->setStyle('width: 256px;');
        foreach ($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $groupCombo->AddEvent(ON_CHANGE, 'changeMenuGroup(this.value);');
        $tpl->SetVariable('lbl_gid', $this::t('GROUP'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $parentCombo =& Piwi::CreateWidget('Combo', 'pid');
        $parentCombo->SetID('pid');
        $parentCombo->setStyle('width: 256px;');
        $parentCombo->AddEvent(ON_CHANGE, 'changeMenuParent(this.value);');
        $tpl->SetVariable('lbl_pid', $this::t('PARENT'));
        $tpl->SetVariable('pid', $parentCombo->Get());

        $gadgetCombo =& Piwi::CreateWidget('Combo', 'gadget');
        $gadgetCombo->SetID('gadget');
        $gadgetCombo->setStyle('width: 256px;');
        $gadgetCombo->AddOption(Jaws::t('URL'), 'url');
        $gDir = ROOT_JAWS_PATH. 'gadgets/';
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget) {
            if (!file_exists($gDir . $gadget['name']. '/Hooks/Menu.php')) {
                continue;
            }

            $objGadget = Jaws_Gadget::getInstance($gadget['name']);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }
            $objHook = $objGadget->hook->load('Menu');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }
            $gadgetCombo->AddOption($gadget['title'], $gadget['name']);
        }

        $gadgetCombo->AddEvent(ON_CHANGE, 'changeGadget(this.value);');
        $tpl->SetVariable('lbl_gadget', Jaws::t('GADGET'));
        $tpl->SetVariable('gadget', $gadgetCombo->Get());

        $rfcCombo =& Piwi::CreateWidget('Combo', 'references');
        $rfcCombo->SetID('references');
        $rfcCombo->setStyle('width: 256px;');
        $rfcCombo->AddEvent(ON_CHANGE, 'changeReferences();');
        $tpl->SetVariable('lbl_references', $this::t('REFERENCES'));
        $tpl->SetVariable('references', $rfcCombo->Get());

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 256px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlEntry->SetStyle('direction: ltr;width: 256px;');
        $tpl->SetVariable('url', $urlEntry->Get());

        $tpl->SetVariable('lbl_options', $this::t('OPTIONS'));
        $optionsEntry =& Piwi::CreateWidget('Entry', 'options', '');
        $optionsEntry->SetStyle('direction: ltr;width: 256px;');
        $tpl->SetVariable('options', $optionsEntry->Get());

        $tpl->SetVariable('lbl_symbol', $this::t('SYMBOL'));
        $symbolEntry =& Piwi::CreateWidget('Entry', 'symbol', '');
        $symbolEntry->SetStyle('direction: ltr;width: 256px;');
        $tpl->SetVariable('symbol', $symbolEntry->Get());

        $tpl->SetVariable('lbl_mega', Jaws::t('MEGA'));
        $mega =& Piwi::CreateWidget('Combo', 'mega');
        $mega->SetID('mega');
        $mega->SetStyle('width: 128px;');
        $mega->AddOption(Jaws::t('NO'),  0);
        $mega->AddOption(Jaws::t('YES'), 1);
        $mega->SetDefault(0);
        $tpl->SetVariable('mega', $mega->Get());

        $target =& Piwi::CreateWidget('Combo', 'target');
        $target->SetID('target');
        $target->setStyle('width: 128px;');
        $target->AddOption($this::t('TARGET_SELF'),  0);
        $target->AddOption($this::t('TARGET_BLANK'), 1);
        $tpl->SetVariable('lbl_target', $this::t('TARGET'));
        $tpl->SetVariable('target', $target->Get());

        $order =& Piwi::CreateWidget('Combo', 'order');
        $order->SetID('order');
        $order->setStyle('width: 128px;');
        $tpl->SetVariable('lbl_order', $this::t('ORDER'));
        $tpl->SetVariable('order', $order->Get());

        $tpl->SetVariable('lbl_status', Jaws::t('STATUS'));
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->SetID('status');
        $status->SetStyle('width: 128px;');
        $status->AddOption(Jaws::t('DISABLED'),  0);
        $status->AddOption(Jaws::t('PUBLISHED'), 1);
        $status->AddOption($this::t('ANONYMOUS'),   2);
        $status->AddOption($this::t('RESTRICTED'),  3);
        $status->SetDefault(1);
        $tpl->SetVariable('status', $status->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'removeImage()');
        $tpl->SetVariable('btn_remove', $button->Get());

        $tpl->ParseBlock('menus/MenusUI');
        $tpl->ParseBlock('menus');
        return $tpl->Get();
    }

    /**
     * Uploads the image file
     *
     * @access  public
     * @return  string  javascript script snippet
     */
    function UploadImage()
    {
        $res = Jaws_FileManagement_File::uploadFiles($_FILES, '', 'gif,jpg,jpeg,png,bmp,ico');
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } elseif (empty($res)) {
            $response = array('type'    => 'error',
                              'message' => Jaws::t('ERROR_UPLOAD_4'));
        } else {
            $response = array('type'    => 'notice',
                              'message' => $res['upload_image'][0]['host_filename']);
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

    /**
     * Returns menu image as stream data
     *
     * @access  public
     * @return  bool    True on successful, False otherwise
     */
    function LoadImage()
    {
        $params = $this->gadget->request->fetch(array('id', 'file'), 'get');

        $objImage = Jaws_Image::factory();
        if (!Jaws_Error::IsError($objImage)) {
            if (!isset($params['file'])) {
                $model = $this->gadget->model->load('Menu');
                $result = $model->GetMenuImage($params['id']);
                if (!Jaws_Error::IsError($result)) {
                    $result = $objImage->setData($result, true);
                }
            } else {
                $params['file'] = preg_replace("/[^[:alnum:]_\.\-]*/i", "", $params['file']);
                $result = $objImage->load(Jaws_FileManagement_File::upload_tmp_dir(). '/'. $params['file'], true);
            }

            if (!Jaws_Error::IsError($result)) {
                $result = $objImage->display();
                if (!Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        }

        return false;
    }
}