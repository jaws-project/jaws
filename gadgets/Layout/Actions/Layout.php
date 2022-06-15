<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
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
        $rqst = $this->gadget->request->fetch(array('theme', 'layout'));
        $layout = empty($rqst['layout'])? 'Layout' : $rqst['layout'];

        // check permissions
        if ($layout == 'Index.User') {
            $this->app->session->checkPermission('Users', 'ManageUserLayout');
            $user = (int)$this->app->session->user->id;
        } else {
            $this->app->session->checkPermission('Layout', 'MainLayoutManage');
            $user = 0;
        }

        // theme
        @list($rqst['theme'], $rqst['locality']) = explode(',', $rqst['theme']);
        $default_theme = (array)$this->gadget->registry->fetch('theme', 'Settings');
        if (empty($rqst['theme']) ||
            ($rqst['locality'] == $default_theme['locality'] && $rqst['theme'] == $default_theme['name'])
        ) {
            $theme = $default_theme['name'];
            $theme_locality = (int)$default_theme['locality'];
        } else {
            $this->gadget->CheckPermission('ManageThemes');
            $this->UpdateTheme($rqst['theme'], $rqst['locality']);
            return Jaws_Header::Location($this->gadget->urlMap('Layout'));
        }
        $this->app->SetTheme($theme, $theme_locality);

        $result = $this->gadget->model->load('Layout')->InitialLayout($layout);
        if (Jaws_Error::IsError($result)) {
            // do nothing!
        }

        $lModel = $this->gadget->model->loadAdmin('Layout');
        $eModel = $this->gadget->model->loadAdmin('Elements');

        $t_item = $this->gadget->template->load('LayoutManager.html');
        $t_item->SetBlock('working_notification');
        $working_box = $t_item->ParseBlock('working_notification');
        $t_item->Blocks['working_notification']->Parsed = '';

        $t_item->SetBlock('response');
        $response = $this->gadget->session->pop('Layout');
        if ($response) {
            $t_item->SetVariable('response_text', $response['text']);
            $t_item->SetVariable('response_type', $response['type']);
        }
        $response_box = $t_item->ParseBlock('response');
        $t_item->Blocks['response']->Parsed = '';

        $t_item->SetBlock('drag_drop');
        $t_item->SetVariable('empty_section',    $this::t('SECTION_EMPTY'));
        $t_item->SetVariable('display_always',   $this::t('ALWAYS'));
        $t_item->SetVariable('display_never',    $this::t('NEVER'));
        $t_item->SetVariable('displayWhenTitle', $this::t('CHANGE_DW'));
        $t_item->SetVariable('actionsTitle',     $this::t('ACTIONS'));
        $t_item->SetVariable('confirmDelete',    $this::t('CONFIRM_DELETE'));
        $dragdrop = $t_item->ParseBlock('drag_drop');
        $t_item->Blocks['drag_drop']->Parsed = '';

        $fakeLayout = new Jaws_Layout();
        $fakeLayout->Load('', "$layout.html");
        $fakeLayout->addScript('gadgets/Layout/Resources/script.js');
        // set default value of javascript variables
        $this->gadget->define(
            'layout_layout_url',
            $this->gadget->urlMap('Layout', array('layout' => '~layout~')),
            'Layout'
        );
        $this->gadget->define(
            'layout_theme_url',
            $this->gadget->urlMap('Layout', array('theme' => '~theme~')),
            'Layout'
        );
        $this->gadget->define('noActionsMsg', $this::t('NO_GADGET_ACTIONS'), 'Layout');
        $this->gadget->define('noItemsMsg', $this::t('SECTION_EMPTY'), 'Layout');
        $this->gadget->define('displayAlways', $this::t('ALWAYS'), 'Layout');
        $this->gadget->define('displayNever', $this::t('NEVER'), 'Layout');
        $this->gadget->define('actionsTitle', $this::t('ACTIONS'), 'Layout');
        $this->gadget->define('displayWhenTitle', $this::t('CHANGE_DW'), 'Layout');
        $this->gadget->define('confirmDelete', $this::t('CONFIRM_DELETE'), 'Layout');

        $layoutContent = $fakeLayout->_Template->Blocks['layout']->Content;

        // remove script tag
        //$layoutContent = preg_replace('@<script[^>]*>.*?</script>@sim', '', $layoutContent);

        $layoutContent = preg_replace(
            '$<body([^>]*)>$i',
            '<body\1>'. $working_box. $response_box. $this->LayoutBar($theme, $theme_locality, $layout),
            $layoutContent
        );
        $layoutContent = preg_replace('$</body([^>]*)>$i', $dragdrop . '</body\1>', $layoutContent);
        $fakeLayout->_Template->Blocks['layout']->Content = $layoutContent;

        $fakeLayout->_Template->SetVariable('site-title', $this->gadget->registry->fetch('site_name', 'Settings'));

        $fakeLayout->addLink(PIWI_URL. 'piwidata/css/default.css');
        $fakeLayout->addLink('gadgets/Layout/Resources/style'.$fakeLayout->_Template->globalVariables['.dir'].'.css');

        foreach ($fakeLayout->_Template->Blocks['layout']->InnerBlock as $name => $data) {
            if ($name == 'head') {
                continue;
            }

            $fakeLayout->_Template->SetBlock('layout/'.$name);
            $gadgets = $lModel->GetGadgetsInSection($layout, $name);
            if (!is_array($gadgets)) {
                continue;
            }

            foreach ($gadgets as $gadget) {
                if ($gadget['gadget'] == '[REQUESTEDGADGET]') {
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('layout', $layout);
                    $t_item->SetVariable('pos', $gadget['position']);
                    $t_item->SetVariable('gadget', $this::t('REQUESTED_GADGET'));
                    $t_item->SetVariable('action', '&nbsp;');
                    $t_item->SetVariable('icon', 'gadgets/Layout/Resources/images/requested-gadget.png');
                    $t_item->SetVariable('description', $this::t('REQUESTED_GADGET_DESC'));
                    $t_item->SetVariable('lbl_when', $this::t('DISPLAY_IN'));
                    $t_item->SetVariable('when', Jaws::t('ALWAYS'));
                    $t_item->SetVariable('void_link', 'return;');
                    $t_item->SetVariable('section_name', $name);
                    $t_item->SetVariable('delete', 'void(0);');
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/Resources/images/no-delete.gif');
                    $t_item->SetVariable('lbl_delete', Jaws::t('DELETE'));
                    $t_item->SetVariable('item_status', 'none');
                    $t_item->ParseBlock('item');
                } else {
                    $controls = '';
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('pos', $gadget['position']);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('base_script_url', $this->app->getSiteURL('/'.BASE_SCRIPT));
                    $t_item->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['gadget'].'/Resources/images/logo.png'));
                    $t_item->SetVariable(
                        'delete',
                        "deleteElement('{$gadget['id']}');"
                    );
                    $t_item->SetVariable('delete-img', 'gadgets/Layout/Resources/images/delete-item.gif');
                    $t_item->SetVariable('lbl_delete', Jaws::t('DELETE'));

                    $actions = $eModel->GetGadgetLayoutActions($gadget['gadget'], true);
                    if (isset($actions[$gadget['action']]) &&
                        Jaws_Gadget::IsGadgetEnabled($gadget['gadget'])
                    ) {
                        $t_item->SetVariable('gadget', $this::t($gadget['gadget']. '.TITLE'));
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
                    $t_item->SetVariable('lbl_when', $this::t('DISPLAY_IN'));
                    if ($gadget['when'] == '*') {
                        $t_item->SetVariable('when', Jaws::t('ALWAYS'));
                    } elseif (empty($gadget['when'])) {
                        $t_item->SetVariable('when', $this::t('NEVER'));
                    } else {
                        $t_item->SetVariable('when', str_replace(',', ', ', $gadget['when']));
                    }
                    $t_item->ParseBlock('item');
                }
            }

            $fakeLayout->_Template->SetVariable(
                'ELEMENT', '<div id="layout_'.$name.'" class="layout-section" title="'.
                $name.'">'.$t_item->Get().'</div>'.
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
    function LayoutBar($theme_name, $theme_locality, $layout = 'Layout')
    {
        $tpl = $this->gadget->template->load('LayoutControls.html');
        $tpl->SetBlock('controls');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('cp-title', Jaws::t('CONTROLPANEL'));
        $tpl->SetVariable('cp-title-separator', Jaws::t('CONTROLPANEL_TITLE_SEPARATOR'));
        if ($this->gadget->GetPermission('default_admin', '', false, 'ControlPanel')) {
            $tpl->SetVariable('admin_script', 'admin.php');
        } else {
            $tpl->SetVariable('admin_script', 'javascript:void();');
        }
        $tpl->SetVariable('title-name', $this::t('TITLE'));
        $tpl->SetVariable('icon-gadget', 'gadgets/Layout/Resources/images/logo.png');
        $tpl->SetVariable('title-gadget', 'Layout');
        $tpl->SetVariable('layout-url', $this->gadget->urlMap('Layout', array()));

        // themes
        $tpl->SetVariable('lbl_theme', $this::t('THEME'));
        $themeCombo =& Piwi::CreateWidget('ComboGroup', 'theme');
        $themeCombo->setID('theme');
        $themeCombo->addGroup(0, $this::t('THEME_LOCAL'));
        $themeCombo->addGroup(1, $this::t('THEME_REMOTE'));
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
        $tpl->SetVariable('lbl_layout', $this::t('LAYOUT'));
        $layouts =& Piwi::CreateWidget('Combo', 'layout');
        $layouts->setID('layout');
        if (isset($themes[$theme_locality][$theme_name])) {
            $theme_layouts = array_flip(
                array_map(
                    'basename',
                    glob(($theme_locality? JAWS_BASE_THEMES : JAWS_THEMES). $theme_name. '/*.html')
                )
            );
            // default layout
            $layouts->AddOption($this::t('LAYOUT_DEFAULT'), 'Layout');
            // index layout
            if (isset($theme_layouts['Index.html'])) {
                $layouts->AddOption($this::t('LAYOUT_INDEX'), 'Index');
            }
            // dashboard layout available if user has permission for use it
            if ($this->app->session->getPermission('Users', 'ManageDashboard') &&
                isset($theme_layouts['Index.Dashboard.html'])
            ) {
                $layouts->AddOption($this::t('DASHBOARD'), 'Index.Dashboard');
            }
            // unset pre-added layouts
            unset(
                $theme_layouts['Layout.html'],
                $theme_layouts['Index.html'],
                $theme_layouts['Index.Dashboard.html']
            );
            // loop for add other layouts
            foreach ($theme_layouts as $theme_layout => $temp) {
                $theme_layout = basename($theme_layout, '.html');
                $layouts->AddOption($theme_layout, $theme_layout);
            }
        }

        $layouts->SetDefault($layout);
        $layouts->AddEvent(ON_CHANGE, "layoutControlsSubmit(this);");
        $tpl->SetVariable('layouts_combo', $layouts->Get());

        $add =& Piwi::CreateWidget('Button', 'add', $this::t('NEW'), STOCK_ADD);
        $url = $this->app->getSiteURL('/').
            BASE_SCRIPT. '?reqGadget=Layout&amp;reqAction=AddLayoutElement&amp;layout='. $layout;
        $add->AddEvent(ON_CLICK, "addGadget('".$url."', '".$this::t('NEW')."');");
        $tpl->SetVariable('add_gadget', $add->Get());

        $docurl = $this->gadget->GetDoc();
        if (!empty($docurl) && !is_null($docurl)) {
            $tpl->SetBlock('controls/documentation');
            $tpl->SetVariable('src', 'images/stock/help-browser.png');
            $tpl->SetVariable('alt', Jaws::t('HELP'));
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
    function UpdateTheme($theme, $theme_locality)
    {
        $theme = preg_replace('/[^[:alnum:]_\-]/', '', $theme);
        $layout_path = ($theme_locality == 0? JAWS_THEMES : JAWS_BASE_THEMES). $theme;
        $tpl = new Jaws_Template(false);
        $tpl->Load('Layout.html', $layout_path);

        // Validate theme
        if (!isset($tpl->Blocks['layout'])) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_BLOCK', $theme, 'layout'),
                RESPONSE_ERROR,
                'Layout'
            );
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['main'])) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_BLOCK', $theme, 'layout/main'),
                RESPONSE_ERROR,
                'Layout'
            );
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['links'])) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_BLOCK', $theme, 'layout/links'),
                RESPONSE_ERROR,
                'Layout'
            );
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['metas'])) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_BLOCK', $theme, 'layout/metas'),
                RESPONSE_ERROR,
                'Layout'
            );
            return false;
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['scripts'])) {
            $this->gadget->session->push(
                $this::t('ERROR_NO_BLOCK', $theme, 'layout/scripts'),
                RESPONSE_ERROR,
                'Layout'
            );
            return false;
        }

        $this->gadget->registry->update(
            'theme',
            array('name' => $theme, 'locality' => $theme_locality),
            null,
            'Settings'
        );
        $this->gadget->session->push(
            $this::t('THEME_CHANGED'),
            RESPONSE_NOTICE,
            'Layout'
        );
    }

}