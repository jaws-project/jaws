<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Layout extends Jaws_Gadget_Action
{
    /**
     * Returns the HTML content to manage the layout in the browser
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Layout()
    {
        $rqst = jaws()->request->fetch(array('user', 'theme', 'layout'));
        $layout = empty($rqst['layout'])? 'Layout' : $rqst['layout'];
        // dashboard_user
        if (empty($rqst['user']) && $this->gadget->GetPermission('ManageLayout')) {
            $user = 0;
        } else {
            $GLOBALS['app']->Session->CheckPermission('Users', 'ManageDashboard');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        // theme
        @list($rqst['theme'], $rqst['locality']) = explode(',', $rqst['theme']);
        $default_theme = unserialize($this->gadget->registry->fetchByUser('theme', 'Settings', $user));

        $layoutModel = $this->gadget->model->load('Layout');
        $layoutModel->DashboardSwitch($user);

        if (empty($rqst['theme']) ||
            ($rqst['locality'] == $default_theme['locality'] && $rqst['theme'] == $default_theme['name'])
        ) {
            $theme = $default_theme['name'];
            $theme_locality = $default_theme['locality'];
        } else {
            $this->gadget->CheckPermission('ManageThemes');
            $this->UpdateTheme($rqst['theme'], $rqst['locality'], $user);
            $default_theme = unserialize($this->gadget->registry->fetchByUser('theme', 'Settings', $user));
            $theme = $default_theme['name'];
            $theme_locality = $default_theme['locality'];
        }
        $GLOBALS['app']->SetTheme($theme, $theme_locality);

        $lModel = $this->gadget->model->loadAdmin('Layout');
        $eModel = $this->gadget->model->loadAdmin('Elements');

        $t_item = $this->gadget->template->load('LayoutManager.html');
        $t_item->SetBlock('working_notification');
        $t_item->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $working_box = $t_item->ParseBlock('working_notification');
        $t_item->Blocks['working_notification']->Parsed = '';

        $t_item->SetBlock('response');
        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            $t_item->SetVariable('text', $responses[0]['text']);
            $t_item->SetVariable('type', $responses[0]['type']);
        }
        $response_box = $t_item->ParseBlock('response');
        $t_item->Blocks['response']->Parsed = '';

        $t_item->SetBlock('drag_drop');
        $t_item->SetVariable('empty_section',    _t('LAYOUT_SECTION_EMPTY'));
        $t_item->SetVariable('display_always',   _t('LAYOUT_ALWAYS'));
        $t_item->SetVariable('display_never',    _t('LAYOUT_NEVER'));
        $t_item->SetVariable('displayWhenTitle', _t('LAYOUT_CHANGE_DW'));
        $t_item->SetVariable('actionsTitle',     _t('LAYOUT_ACTIONS'));
        $t_item->SetVariable('confirmDelete',    _t('LAYOUT_CONFIRM_DELETE'));
        $dragdrop = $t_item->ParseBlock('drag_drop');
        $t_item->Blocks['drag_drop']->Parsed = '';

        // Init layout
        $GLOBALS['app']->InstanceLayout();

        $fakeLayout = new Jaws_Layout();
        $fakeLayout->Load('', "$layout.html");
        $fakeLayout->AddScriptLink('gadgets/Layout/Resources/script.js');

        $layoutContent = $fakeLayout->_Template->Blocks['layout']->Content;
        // remove script tag
        $layoutContent = preg_replace('@<script[^>]*>.*</script>@sim', '', $layoutContent);

        $layoutContent = preg_replace(
            '$<body([^>]*)>$i',
            '<body\1>'. $working_box. $response_box. $this->LayoutBar($theme, $theme_locality, $user, $layout),
            $layoutContent
        );
        $layoutContent = preg_replace('$</body([^>]*)>$i', $dragdrop . '</body\1>', $layoutContent);
        $fakeLayout->_Template->Blocks['layout']->Content = $layoutContent;

        $fakeLayout->_Template->SetVariable('site-title', $this->gadget->registry->fetch('site_name', 'Settings'));

        $fakeLayout->AddHeadLink(
            PIWI_URL. 'piwidata/css/default.css',
            'stylesheet',
            'text/css',
            'default'
        );
        $fakeLayout->AddHeadLink(
            'gadgets/Layout/Resources/style.css',
            'stylesheet',
            'text/css'
        );

        foreach ($fakeLayout->_Template->Blocks['layout']->InnerBlock as $name => $data) {
            if ($name == 'head') {
                continue;
            }

            $fakeLayout->_Template->SetBlock('layout/'.$name);
            $js_section_array = '<script type="text/javascript">items[\''.$name.'\'] = new Array(); sections.push(\''.$name.'\');</script>';
            $gadgets = $lModel->GetGadgetsInSection($layout, $name, $user);
            if (!is_array($gadgets)) {
                continue;
            }

            foreach ($gadgets as $gadget) {
                if ($gadget['gadget'] == '[REQUESTEDGADGET]') {
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('user', $user);
                    $t_item->SetVariable('pos', $gadget['position']);
                    $t_item->SetVariable('gadget', _t('LAYOUT_REQUESTED_GADGET'));
                    $t_item->SetVariable('action', '&nbsp;');
                    $t_item->SetVariable('icon', 'gadgets/Layout/Resources/images/requested-gadget.png');
                    $t_item->SetVariable('description', _t('LAYOUT_REQUESTED_GADGET_DESC'));
                    $t_item->SetVariable('lbl_when', _t('LAYOUT_DISPLAY_IN'));
                    $t_item->SetVariable('when', _t('GLOBAL_ALWAYS'));
                    $t_item->SetVariable('void_link', 'return;');
                    $t_item->SetVariable('section_name', $name);
                    $t_item->SetVariable('delete', 'void(0);');
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/Resources/images/no-delete.gif');
                    $t_item->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
                    $t_item->SetVariable('item_status', 'none');
                    $t_item->ParseBlock('item');
                } else {
                    $controls = '';
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('pos', $gadget['position']);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('base_script_url', $GLOBALS['app']->getSiteURL('/'.BASE_SCRIPT));
                    $t_item->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['gadget'].'/Resources/images/logo.png'));
                    $t_item->SetVariable(
                        'delete',
                        "deleteElement('{$gadget['id']}');"
                    );
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/Resources/images/delete-item.gif');
                    $t_item->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));

                    $actions = $eModel->GetGadgetLayoutActions($gadget['gadget'], true);
                    if (isset($actions[$gadget['action']]) &&
                        Jaws_Gadget::IsGadgetEnabled($gadget['gadget'])
                    ) {
                        $t_item->SetVariable('gadget', _t(strtoupper($gadget['gadget']).'_TITLE'));
                        if (isset($actions[$gadget['action']]['name'])) {
                            $t_item->SetVariable('action', $actions[$gadget['action']]['name']);
                        } else {
                            $t_item->SetVariable('action', $gadget['action']);
                        }
                        $t_item->SetVariable('description', $actions[$gadget['action']]['desc']);
                        $t_item->SetVariable('item_status', 'none');
                    } else {
                        $t_item->SetVariable('gadget', $gadget['gadget']);
                        $t_item->SetVariable('action', $gadget['action']);
                        $t_item->SetVariable('description', $gadget['action']);
                        $t_item->SetVariable('item_status', 'line-through');
                    }
                    unset($actions);

                    $t_item->SetVariable('controls', $controls);
                    $t_item->SetVariable('void_link', '');
                    $t_item->SetVariable('lbl_when', _t('LAYOUT_DISPLAY_IN'));
                    if ($gadget['when'] == '*') {
                        $t_item->SetVariable('when', _t('GLOBAL_ALWAYS'));
                    } elseif (empty($gadget['when'])) {
                        $t_item->SetVariable('when', _t('LAYOUT_NEVER'));
                    } else {
                        $t_item->SetVariable('when', str_replace(',', ', ', $gadget['when']));
                    }
                    $t_item->ParseBlock('item');
                }
            }

            $fakeLayout->_Template->SetVariable(
                'ELEMENT', '<div id="layout_'.$name.'" class="layout-section" title="'.
                $name.'">'.$js_section_array.$t_item->Get().'</div>'.
                '<div class="layout-section-controls"></div>'
            );

            $fakeLayout->_Template->ParseBlock('layout/'.$name);
            $t_item->Blocks['item']->Parsed = '';
        }

        return $fakeLayout->Get(true);
    }

    /**
     * Layout controls bar 
     *
     */
    function LayoutBar($theme_name, $theme_locality, $user = 0, $layout = 'Layout')
    {
        $tpl = $this->gadget->template->load('LayoutControls.html');
        $tpl->SetBlock('controls');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('cp-title', _t('GLOBAL_CONTROLPANEL'));
        $tpl->SetVariable('cp-title-separator', _t('GLOBAL_CONTROLPANEL_TITLE_SEPARATOR'));
        if ($this->gadget->GetPermission('default_admin', '', false, 'ControlPanel')) {
            $tpl->SetVariable('admin_script', 'admin.php');
        } else {
            $tpl->SetVariable('admin_script', 'javascript:void();');
        }
        $tpl->SetVariable('title-name', _t('LAYOUT_TITLE'));
        $tpl->SetVariable('icon-gadget', 'gadgets/Layout/Resources/images/logo.png');
        $tpl->SetVariable('title-gadget', 'Layout');
        $tpl->SetVariable('layout-url', $this->gadget->urlMap('Layout', array()));

        // dashboards
        $tpl->SetVariable('lbl_dashboard', _t('LAYOUT_DASHBOARD'));
        $dashboards =& Piwi::CreateWidget('Combo', 'user');
        $dashboards->setID('user');
        if ($this->gadget->GetPermission('ManageLayout')) {
            $dashboards->AddOption(_t('LAYOUT_DASHBOARD_GLOBAL'), 0);
        }
        if ($GLOBALS['app']->Session->GetPermission('Users', 'ManageDashboard')) {
            $dashboards->AddOption(
                _t('LAYOUT_DASHBOARD_USER'),
                (int)$GLOBALS['app']->Session->GetAttribute('user')
            );
        }
        $dashboards->SetDefault($user);
        $dashboards->AddEvent(ON_CHANGE, "layoutControlsSubmit(this);");
        $tpl->SetVariable('dashboards_combo', $dashboards->Get());

        // themes
        $tpl->SetVariable('lbl_theme', _t('LAYOUT_THEME'));
        $themeCombo =& Piwi::CreateWidget('ComboGroup', 'theme');
        $themeCombo->setID('theme');
        $themeCombo->addGroup(0, _t('LAYOUT_THEME_LOCAL'));
        $themeCombo->addGroup(1, _t('LAYOUT_THEME_REMOTE'));
        $themes = Jaws_Utils::GetThemesInfo();
        foreach ($themes[0] as $theme => $tInfo) {
            $themeCombo->AddOption(0, $tInfo['name'], "$theme,0");
        }
        foreach ($themes[1] as $theme => $tInfo) {
            $themeCombo->AddOption(1, $tInfo['name'], "$theme,1");
        }
        $themeCombo->SetDefault("$theme_name,$theme_locality");
        $themeCombo->AddEvent(ON_CHANGE, "layoutControlsSubmit(this);");
        $themeCombo->SetEnabled($this->gadget->GetPermission('ManageThemes'));
        $tpl->SetVariable('theme_combo', $themeCombo->Get());

        // layouts
        $tpl->SetVariable('lbl_layout', _t('LAYOUT_LAYOUT'));
        $layouts =& Piwi::CreateWidget('Combo', 'layout');
        $layouts->setID('layout');
        if (isset($themes[$theme_locality][$theme_name])) {
            $theme_layouts = array_flip(
                array_map(
                    'basename',
                    glob(($theme_locality? JAWS_BASE_THEMES : JAWS_THEMES). $theme_name. '/*.html')
                )
            );
            $layouts->AddOption(_t('LAYOUT_LAYOUT_DEFAULT'), 'Layout');
            if (isset($theme_layouts['Index.html'])) {
                $layouts->AddOption(_t('LAYOUT_LAYOUT_INDEX'), 'Index');
            }
            unset($theme_layouts['Layout.html'], $theme_layouts['Index.html']);
            foreach ($theme_layouts as $theme_layout => $temp) {
                $theme_layout = basename($theme_layout, '.html');
                $layouts->AddOption($theme_layout, $theme_layout);
            }
        }
        
        $layouts->SetDefault($layout);
        $layouts->AddEvent(ON_CHANGE, "layoutControlsSubmit(this);");
        $tpl->SetVariable('layouts_combo', $layouts->Get());

        $add =& Piwi::CreateWidget('Button', 'add', _t('LAYOUT_NEW'), STOCK_ADD);
        $url = $GLOBALS['app']->getSiteURL('/').
            BASE_SCRIPT. '?gadget=Layout&amp;action=AddLayoutElement&amp;user='. $user;
        $add->AddEvent(ON_CLICK, "addGadget('".$url."', '"._t('LAYOUT_NEW')."');");
        $tpl->SetVariable('add_gadget', $add->Get());

        $docurl = $this->gadget->GetDoc();
        if (!empty($docurl) && !is_null($docurl)) {
            $tpl->SetBlock('controls/documentation');
            $tpl->SetVariable('src', 'images/stock/help-browser.png');
            $tpl->SetVariable('alt', _t('GLOBAL_HELP'));
            $tpl->SetVariable('url', $docurl);
            $tpl->ParseBlock('controls/documentation');
        }

        $tpl->ParseBlock('controls');
        return $tpl->Get();
    }

    /**
     *
     *
     */
    function UpdateTheme($theme, $theme_locality, $user)
    {
        $theme = preg_replace('/[^[:alnum:]_\-]/', '', $theme);
        $layout_path = ($theme_locality == 0? JAWS_THEMES : JAWS_BASE_THEMES). $theme;
        $tpl = $this->gadget->template->load("$layout_path/layout.html");

        // Validate theme
        if (!isset($tpl->Blocks['layout'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'layout'), RESPONSE_ERROR);
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['head'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'head'), RESPONSE_ERROR);
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['main'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'main'), RESPONSE_ERROR);
            return false;
        }

        // Verify blocks/Reassign gadgets
        $model = $this->gadget->model->loadAdmin('Sections');
        $layouts = $model->GetLayoutLayouts($user);
        if (Jaws_Error::IsError($layouts)) {
            $GLOBALS['app']->Session->PushLastResponse($layouts->getMessage(), RESPONSE_ERROR);
            return false;
        }

        foreach ($layouts as $layout) {
            if (!file_exists($layout_path. "/$layout.html")) {
                $model->DeleteLayouts($layout);
            } else {
                $tpl = $this->gadget->template->load("$layout_path/$layout.html");
                $sections = $model->GetLayoutSections($layout);
                if (Jaws_Error::IsError($sections)) {
                    $GLOBALS['app']->Session->PushLastResponse($sections->getMessage(), RESPONSE_ERROR);
                    return false;
                }

                foreach ($sections as $section) {
                    if (!isset($tpl->Blocks['layout']->InnerBlock[$section])) {
                        $model->MoveSection($layout, $section, 'main');
                    }
                }
            }
        }

        $this->gadget->registry->updateByUser(
            'theme',
            serialize(array('name' => $theme, 'locality' => $theme_locality)),
            'Settings',
            $user
        );
        $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_THEME_CHANGED'), RESPONSE_NOTICE);
    }

}