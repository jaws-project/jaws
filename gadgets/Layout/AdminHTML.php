<?php
/**
 * Layout Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LayoutAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Returns the HTML content to manage the layout in the browser
     *
     * @access  public
     * @return  string  HTML conent of Layout Management
     */
    function Admin()
    {
        return $this->LayoutManager();
    }

    function LayoutManager()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');

        $t_item = new Jaws_Template('gadgets/Layout/templates/');
        $t_item->Load('LayoutManager.html');

        $t_item->SetBlock('working_notification');
        $t_item->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $working_box = $t_item->ParseBlock('working_notification');
        $t_item->Blocks['working_notification']->Parsed = '';

        $t_item->SetBlock('msgbox-wrapper');
        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            foreach ($responses as $msg_id => $response) {
                $t_item->SetBlock('msgbox-wrapper/msgbox');
                $t_item->SetVariable('msg-css', $response['css']);
                $t_item->SetVariable('msg-txt', $response['message']);
                $t_item->SetVariable('msg-id', $msg_id);
                $t_item->ParseBlock('msgbox-wrapper/msgbox');
            }
        }
        $msg_box = $t_item->ParseBlock('msgbox-wrapper');
        $t_item->Blocks['msgbox-wrapper']->Parsed = '';

        $t_item->SetBlock('drag_drop');
        $t_item->SetVariable('empty_section',    _t('LAYOUT_SECTION_EMPTY'));
        $t_item->SetVariable('display_always',   _t('LAYOUT_ALWAYS'));
        $t_item->SetVariable('display_never',    _t('LAYOUT_NEVER'));
        $t_item->SetVariable('displayWhenTitle', _t('LAYOUT_CHANGE_DW'));
        $t_item->SetVariable('actionsTitle',     _t('LAYOUT_ACTIONS'));
        $dragdrop = $t_item->ParseBlock('drag_drop');
        $t_item->Blocks['drag_drop']->Parsed = '';

        // Init layout
        $GLOBALS['app']->InstanceLayout();

        $fakeLayout = new Jaws_Layout();
        $fakeLayout->Load();
        $fakeLayout->AddScriptLink('libraries/prototype/prototype.js');
        $fakeLayout->AddScriptLink('libraries/prototype/scriptaculous.js');
        $fakeLayout->AddScriptLink('include/Jaws/Ajax/Ajax.js');
        $fakeLayout->AddScriptLink(BASE_SCRIPT . '?gadget=Layout&action=Ajax&client');
        $fakeLayout->AddScriptLink('gadgets/Layout/resources/script.js');

        $layoutContent = $fakeLayout->_Template->Blocks['layout']->Content;
        $useLayoutMode = $fakeLayout->_Template->VariableExists('layout-mode');
        $layoutContent = preg_replace(
                            '$<body([^>]*)>$i',
                            '<body\1>' . $working_box . $msg_box . $this->getLayoutControls($useLayoutMode),
                            $layoutContent);
        $layoutContent = preg_replace('$</body([^>]*)>$i', $dragdrop . '</body\1>', $layoutContent);
        $fakeLayout->_Template->Blocks['layout']->Content = $layoutContent;

        $fakeLayout->_Template->SetVariable('site-title', $GLOBALS['app']->Registry->Get('/config/site_name'));

        $fakeLayout->AddHeadLink(PIWI_URL . 'piwidata/css/default.css', 'stylesheet', 'text/css', 'default');
        $fakeLayout->AddHeadLink('gadgets/Layout/resources/style.css', 'stylesheet', 'text/css');

        $fakeLayout->addHeadOther(
                    '<!--[if lt IE 7]>'."\n".
                    '<script src="gadgets/ControlPanel/resources/ie-bug-fix.js" type="text/javascript"></script>'."\n".
                    '<![endif]-->');

        foreach ($fakeLayout->_Template->Blocks['layout']->InnerBlock as $name => $data) {
            if ($name == 'head') continue;
            $fakeLayout->_Template->SetBlock('layout/'.$name);
            $js_section_array = '<script type="text/javascript">items[\''.$name.'\'] = new Array(); sections.push(\''.$name.'\');</script>';
            $gadgets = $model->GetGadgetsInSection($name);
            if (!is_array($gadgets)) continue;
            foreach ($gadgets as $gadget) {
                $id = $gadget['id'];
                if (file_exists(JAWS_PATH . 'gadgets/'. $gadget['gadget']. '/'. 'LayoutHTML.php') ||
                     file_exists(JAWS_PATH . 'gadgets/'. $gadget['gadget']. '/'. 'Actions.php') ||
                    ($gadget['gadget'] == '[REQUESTEDGADGET]'))
                {
                    if (($GLOBALS['app']->Registry->Get('/gadgets/'.$gadget['gadget'].'/enabled') == 'true') ||
                        ($gadget['gadget'] == '[REQUESTEDGADGET]'))
                    {
                        if ($gadget['gadget'] == '[REQUESTEDGADGET]') {
                            $section_empty = false;
                            $t_item->SetBlock('item');
                            $t_item->SetVariable('section_id', $name);
                            $t_item->SetVariable('item_id', $id);
                            $t_item->SetVariable('pos', $gadget['layout_position']);
                            $t_item->SetVariable('gadget', _t('LAYOUT_REQUESTED_GADGET'));
                            $t_item->SetVariable('action', '&nbsp;');
                            $t_item->SetVariable('icon', 'gadgets/Layout/images/requested-gadget.png');
                            $t_item->SetVariable('description', _t('LAYOUT_REQUESTED_GADGET_DESC'));
                            $t_item->SetVariable('lbl_display_when', _t('LAYOUT_DISPLAY_IN'));
                            $t_item->SetVariable('display_when', _t('GLOBAL_ALWAYS'));
                            $t_item->SetVariable('void_link', 'return;');
                            $t_item->SetVariable('section_name', $name);
                            $t_item->SetVariable('delete', 'void(0);');
                            $t_item->SetVariable('delete-img', 'gadgets/Layout/images/no-delete.gif');
                            $t_item->SetVariable('item_status', 'none');
                            $t_item->ParseBlock('item');
                        } else {
                            if (Jaws_Gadget::IsGadgetUpdated($gadget['gadget'])) {
                                $section_empty = false;
                                $controls = '';
                                $t_item->SetBlock('item');
                                $t_item->SetVariable('section_id', $name);
                                $delete_url = "javascript: deleteElement('".$gadget['id']."','"._t('LAYOUT_CONFIRM_DELETE')."');";

                                $actions = $model->GetGadgetLayoutActions($gadget['gadget'], true);
                                if (empty($actions)) {
                                    $t_item->SetVariable('gadget', $gadget['gadget']);
                                    $t_item->SetVariable('action', _t('LAYOUT_ACTIONS'));
                                } else {
                                    $info = $GLOBALS['app']->LoadGadget($gadget['gadget'], 'Info');
                                    $t_item->SetVariable('gadget', $info->GetName());
                                    if (isset($actions[$gadget['gadget_action']]['name'])) {
                                        $t_item->SetVariable('action', $actions[$gadget['gadget_action']]['name']);
                                    } else {
                                        $t_item->SetVariable('action', $gadget['gadget_action']);
                                    }
                                    unset($info);
                                }
                                $t_item->SetVariable('pos', $gadget['layout_position']);
                                $t_item->SetVariable('item_id', $id);
                                $t_item->SetVariable('base_script_url', $GLOBALS['app']->getSiteURL('/'.BASE_SCRIPT));
                                $t_item->SetVariable('icon', 'gadgets/'.$gadget['gadget'].'/images/logo.png');
                                $t_item->SetVariable('delete', 'deleteElement(\''.$gadget['id'].'\',\''._t('LAYOUT_CONFIRM_DELETE').'\');');
                                $t_item->SetVariable('delete-img', 'gadgets/Layout/images/delete-item.gif');
                                if (isset($actions[$gadget['gadget_action']])) {
                                    $t_item->SetVariable('description', $actions[$gadget['gadget_action']]['desc']);
                                    $t_item->SetVariable('item_status', 'none');
                                } else {
                                    $t_item->SetVariable('description', $gadget['gadget_action']);
                                    $t_item->SetVariable('item_status', 'line-through');
                                }
                                unset($actions);

                                $t_item->SetVariable('controls', $controls);
                                $t_item->SetVariable('void_link', '');
                                $t_item->SetVariable('lbl_display_when', _t('LAYOUT_DISPLAY_IN'));
                                if ($gadget['display_when'] == '*') {
                                    $t_item->SetVariable('display_when', _t('GLOBAL_ALWAYS'));
                                } elseif (empty($gadget['display_when'])) {
                                        $t_item->SetVariable('display_when', _t('LAYOUT_NEVER'));
                                } else {
                                    $t_item->SetVariable('display_when', str_replace(',', ', ', $gadget['display_when']));
                                }
                                $t_item->ParseBlock('item');
                            }
                        }
                    }
                }
            }

            $fakeLayout->_Template->SetVariable('ELEMENT', '<div class="layout-section" id="layout_'.$name.'_drop" title="'.$name.'">
                                    <div id="layout_'.$name.'">'.$js_section_array.$t_item->Get().
                                    '</div></div>');

            $fakeLayout->_Template->ParseBlock('layout/'.$name);
            $t_item->Blocks['item']->Parsed = '';
        }

        return $fakeLayout->Get(true);
    }

    function getLayoutControls($useLayoutMode)
    {
        $GLOBALS['app']->LoadGadget('ControlPanel', 'AdminHTML');

        $tpl = new Jaws_Template('gadgets/Layout/templates/');
        $tpl->Load('LayoutControls.html');
        $tpl->SetBlock('controls');
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $gInfo  = $GLOBALS['app']->loadGadget('Layout', 'Info');
        $docurl = null;
        if (!Jaws_Error::isError($gInfo)) {
            $docurl = $gInfo->GetDoc();
        }

        $tpl->SetVariable('admin_script', BASE_SCRIPT);
        $tpl->SetVariable('title-cp', _t('CONTROLPANEL_NAME'));
        $tpl->SetVariable('title-name', _t('LAYOUT_NAME'));
        $tpl->SetVariable('icon-gadget', 'gadgets/Layout/images/logo.png');
        $tpl->SetVariable('title-gadget', 'Layout');

        $tpl->SetVariable('theme', _t('LAYOUT_THEME'));
        $themeCombo =& Piwi::CreateWidget('ComboGroup', 'theme');
        $themeCombo->setID('theme');
        $themeCombo->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
        $themeCombo->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $themes = Jaws_Utils::GetThemesList();
        foreach ($themes as $theme => $tInfo) {
            $themeCombo->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
        }
        $themeCombo->SetDefault($GLOBALS['app']->Registry->Get('/config/theme'));
        $themeCombo->AddEvent(ON_CHANGE, "changeTheme();");
        $themeCombo->SetEnabled($this->GetPermission('ManageThemes'));
        $tpl->SetVariable('theme_combo', $themeCombo->Get());

        if ($useLayoutMode) {
            $tpl->SetVariable('mode', _t('LAYOUT_MODE').':');
            $modeCombo =& Piwi::CreateWidget('ComboImage', 'mode');
            $modeCombo->AddEvent(ON_CHANGE, 'changeLayoutMode();');
            $modeCombo->SetImageSize(16, 16);
            $modeCombo->AddOption(_t('LAYOUT_MODE_TWOBAR'),   1, 'gadgets/Layout/images/layout1.png');
            $modeCombo->AddOption(_t('LAYOUT_MODE_LEFTBAR'),  2, 'gadgets/Layout/images/layout2.png');
            $modeCombo->AddOption(_t('LAYOUT_MODE_RIGHTBAR'), 3, 'gadgets/Layout/images/layout3.png');
            $modeCombo->AddOption(_t('LAYOUT_MODE_NOBAR'),    4, 'gadgets/Layout/images/layout4.png');
            $modeCombo->SetDefault($GLOBALS['app']->Registry->Get('/config/layoutmode'));
            $modeCombo->SetEnabled($this->GetPermission('ManageThemes'));
            $tpl->SetVariable('mode_combo', $modeCombo->Get());
        }

        $add =& Piwi::CreateWidget('Button', 'add', _t('LAYOUT_NEW'), STOCK_ADD);
        $url = $GLOBALS['app']->getSiteURL().'/'.BASE_SCRIPT.'?gadget=Layout&amp;action=AddLayoutElement&amp;mode=new';
        $add->AddEvent(ON_CLICK, "addGadget('".$url."', '"._t('LAYOUT_NEW')."');");
        $tpl->SetVariable('add_gadget', $add->Get());

        if (!empty($docurl) && !is_null($docurl)) {
            $tpl->SetBlock('controls/documentation');
            $tpl->SetVariable('src', 'images/stock/help-browser.png');
            $tpl->SetVariable('alt', _t('GLOBAL_READ_DOCUMENTATION'));
            $tpl->SetVariable('url', $docurl);
            $tpl->ParseBlock('controls/documentation');
        }

        $tpl->ParseBlock('controls');
        return $tpl->Get();
    }

    function ChangeTheme()
    {
        $this->CheckPermission('ManageThemes');

        $request =& Jaws_Request::getInstance();
        $theme = $request->get('theme', 'post');
        $mode = $request->get('mode', 'post');

        $tpl = new Jaws_Template('', JAWS_OTHERS);
        $layout_file = JAWS_DATA . 'themes/' . $theme . '/layout.html';
        if (!file_exists($layout_file)) {
            $layout_file = JAWS_BASE_DATA . 'themes/' . $theme . '/layout.html';
        }
        $tpl->Load($layout_file, false, false);

        // Validate theme
        if (!isset($tpl->Blocks['layout'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'layout'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['head'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'head'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['main'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'main'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
        }

        // Verify blocks/Reassign gadgets
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
        $sections = $model->GetLayoutSections();

        // Backwards compatibility for layoutmode
        if ($mode != $GLOBALS['app']->Registry->Get('/config/layoutmode')) {
            switch($mode) {
                // Two bars...
                case 1: 
                        // Do nothing...
                        break;
                // Left bar
                case 2: 
                        // Disable right bar (bar2)
                        if (isset($tpl->Blocks['layout']->InnerBlock['bar2'])) {
                            $tpl->Blocks['layout']->InnerBlock['bar2'] = null;
                        }
                        break;
                // Right bar 
                case 3: 
                        // Disable left bar (bar1)
                        if (isset($tpl->Blocks['layout']->InnerBlock['bar1'])) {
                            $tpl->Blocks['layout']->InnerBlock['bar1'] = null;
                        }
                        break;
                // No bars
                case 4:
                        // Disable left bar (bar1)
                        if (isset($tpl->Blocks['layout']->InnerBlock['bar1'])) {
                            $tpl->Blocks['layout']->InnerBlock['bar1'] = null;
                        }
                        // Disable right bar (bar2)
                        if (isset($tpl->Blocks['layout']->InnerBlock['bar2'])) {
                            $tpl->Blocks['layout']->InnerBlock['bar2'] = null;
                        }
                        break;
            }
        }

        foreach ($sections as $s) {
            if (!isset($tpl->Blocks['layout']->InnerBlock[$s['section']])) {
                if (isset($tpl->Blocks['layout']->InnerBlock[$s['section'] . '_narrow'])) {
                    $model->MoveSection($s['section'], $s['section'] . '_narrow');
                } elseif (isset($tpl->Blocks['layout']->InnerBlock[$s['section'] . '_wide'])) {
                    $model->MoveSection($s['section'], $s['section'] . '_wide');
                } else {
                    if (strpos($s['section'], '_narrow')) {
                        $clear_section = str_replace('_narrow', '', $s['section']);
                    } else {
                        $clear_section = str_replace('_wide', '', $s['section']);
                    }
                    if (isset($tpl->Blocks['layout']->InnerBlock[$clear_section])) {
                        $model->MoveSection($s['section'], $clear_section);
                    } else {
                        $model->MoveSection($s['section'], 'main');
                    }
                }
            }
        }
        
        $GLOBALS['app']->Registry->Set('/config/theme', $theme);

        // Save mode if exists...
        if ($mode != '') {
            $GLOBALS['app']->Registry->Set('/config/layoutmode', $mode);
        }
        $GLOBALS['app']->Registry->Commit('core');
        $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_THEME_CHANGED'), RESPONSE_NOTICE);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
    }

    /**
     * Adds layout element
     *
     * @access  public
     * @return  XHTML template content
     */
    function AddLayoutElement()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');

        // FIXME: When a gadget don't have layout actions
        // doesn't permit to add it into layout
        $tpl = new Jaws_Template('gadgets/Layout/templates/');
        $tpl->Load('AddGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->SetVariable('gadgets', _t('LAYOUT_GADGETS'));
        $tpl->SetVariable('actions', _t('LAYOUT_ACTIONS'));
        $tpl->SetVariable('no_actions_msg', _t('LAYOUT_NO_GADGET_ACTIONS'));
        $addButton =& Piwi::CreateWidget('Button', 'add',_t('LAYOUT_NEW'), STOCK_ADD);
        $addButton->AddEvent(ON_CLICK, "getAction();");
        $tpl->SetVariable('add_button', $addButton->Get());

        $request =& Jaws_Request::getInstance();
        $section = $request->get('section', 'post');
        if (is_null($section)) {
            $section = $request->get('section', 'get');
            $section = !is_null($section) ? $section : '';
        }

        $tpl->SetVariable('section', $section);

        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadget_list = $jms->GetGadgetsList(null, true, true, true);

        //Hold.. if we dont have a selected gadget?.. like no gadgets?
        if (count($gadget_list) <= 0) {
            Jaws_Error::Fatal('You don\'t have any installed gadgets, please enable/install one and then come back',
                             __FILE__, __LINE__);
        }
        
        reset($gadget_list);
        $first = current($gadget_list);
        $tpl->SetVariable('first', $first['realname']);

        $tpl->SetBlock('template/working_notification');
        $tpl->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $tpl->ParseBlock('template/working_notification');

        foreach ($gadget_list as $gadget) {
            $tpl->SetBlock('template/gadget');
            $tpl->SetVariable('id',     $gadget['realname']);
            $tpl->SetVariable('icon',   'gadgets/'.$gadget['realname'].'/images/logo.png');
            $tpl->SetVariable('gadget', $gadget['name']);
            $tpl->SetVariable('desc',   $gadget['description']);
            $tpl->ParseBlock('template/gadget');
        }

        $tpl->ParseBlock('template');

        return $tpl->Get();
    }

    /**
     * Save layout element
     *
     * @access  public
     * @return  XHTML template content
     */
    function SaveLayoutElement()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $fields = array('gadget_field', 'action_field', 'section');
        $post = $request->get($fields, 'post');

        // Check that the gadget had an action set.
        if (!is_null($post['action_field'])) {
            $model->NewElement($post['section'], $post['gadget_field'], $post['action_field']);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
    }

    /**
     * Changes action of a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function EditElementAction()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');
        $model = $GLOBALS['app']->LoadGadget('Layout', 'AdminModel');
        $layoutElement = $model->GetElement($id);
        if (!$layoutElement || !isset($layoutElement['id'])) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/Layout/templates/');
        $tpl->Load('EditGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $gInfo = $GLOBALS['app']->LoadGadget($layoutElement['gadget'], 'Info');
        $tpl->SetVariable('gadget', $layoutElement['gadget']);
        $tpl->SetVariable('gadget_name', $gInfo->GetName());
        $tpl->SetVariable('gadget_description', $gInfo->GetDescription());

        $btnSave =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "getAction('{$id}', '{$layoutElement['gadget']}');");
        $tpl->SetVariable('save', $btnSave->Get());

        $actionsList =& Piwi::CreateWidget('RadioButtons', 'action_field', 'vertical');
        $actions = $model->GetGadgetLayoutActions($layoutElement['gadget']);
        if (count($actions) > 0) {
            foreach ($actions as $aIndex => $action) {
                $tpl->SetBlock('template/gadget_action');
                $tpl->SetVariable('index',  $aIndex);
                $tpl->SetVariable('name',   $action['name']);
                $tpl->SetVariable('action', $action['action']);
                $tpl->SetVariable('desc',   $action['desc']);
                $action_selected = $layoutElement['gadget_action'] == $action['action'];
                if($action_selected) {
                    $tpl->SetVariable('action_checked', 'checked="checked"');
                } else {
                    $tpl->SetVariable('action_checked', '');
                }

                if (!empty($action['params'])) {
                    $action_params = unserialize($layoutElement['action_params']);
                    foreach ($action['params'] as $pIndex => $param) {
                        $tpl->SetBlock('template/gadget_action/action_param');
                        $select =& Piwi::CreateWidget('Combo', $param['title']);
                        $select->SetID('');
                        foreach ($param['value'] as $value => $title) {
                            $select->AddOption($title, $value);
                        }
                        if ($action_selected) {
                            $select->SetDefault($action_params[$pIndex]);
                        }
                        $tpl->SetVariable('param', $select->Get());
                        $tpl->ParseBlock('template/gadget_action/action_param');
                    }
                }

                $tpl->ParseBlock('template/gadget_action');
            }
        } else {
            $tpl->SetBlock('template/no_action');
            $tpl->SetVariable('no_gadget_desc', _t('LAYOUT_NO_GADGET_ACTIONS'));
            $tpl->ParseBlock('template/no_action');
        }

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

    /**
     * Changes when to display a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function ChangeDisplayWhen()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');

        $tpl = new Jaws_Template('gadgets/Layout/templates/');
        $tpl->Load('DisplayWhen.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('display_when', _t('LAYOUT_DISPLAY'));

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $layoutElement = $model->GetElement($id);
        if (is_array($layoutElement) && !empty($layoutElement)) {
            $dw_value = $layoutElement['display_when'];
        }

        $displayCombo =& Piwi::CreateWidget('Combo', 'display_in');
        $displayCombo->AddOption(_t('LAYOUT_ALWAYS'), 'always');
        $displayCombo->AddOption(_t('LAYOUT_ONLY_IN_GADGET'), 'selected');

        if ($dw_value == '*') {
            $displayCombo->SetDefault('always');
            $tpl->SetVariable('selected_display', 'none');
        } else {
            $displayCombo->SetDefault('selected');
            $tpl->SetVariable('selected_display', 'block');
        }
        $displayCombo->AddEvent(ON_CHANGE, "showGadgets();");
        $tpl->SetVariable('display_in_combo', $displayCombo->Get());

        // Display in list
        $selectedGadgets = explode(',', $dw_value);
        // for index...
        $gadget_field =& Piwi::CreateWidget('CheckButtons', 'checkbox_index', 'vertical');
        $gadget_field->AddOption(_t('LAYOUT_INDEX'), 'index', null, in_array('index', $selectedGadgets));
        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $gadget_list = $jms->GetGadgetsList(null, true, true, true);
        foreach ($gadget_list as $g) {
            $gadget_field->AddOption($g['name'], $g['realname'], null, in_array($g['realname'], $selectedGadgets));
        }
        $tpl->SetVariable('selected_gadgets', $gadget_field->Get());

        $saveButton =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "parent.parent.saveChangeDW(".$id.", getSelectedGadgets());");
        $tpl->SetVariable('save', $saveButton->Get());

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

    /**
     * Delete an element from the layout
     *
     * @access  public
     * @return  XHTML template content
     */
    function DeleteLayoutElement()
    {
        $model = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $model->DeleteElement($id);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout&action=Admin');
    }

}