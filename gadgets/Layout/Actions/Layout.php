<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
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
        // fetch current layout user
        $layout_user = $GLOBALS['app']->Session->GetAttribute('layout');
        if (empty($layout_user)) {
            $this->gadget->CheckPermission('ManageLayout');
        } else {
            $GLOBALS['app']->Session->CheckPermission('Users', 'ManageDashboard');
        }

        $lModel = $this->gadget->model->loadAdmin('Layout');
        $eModel = $this->gadget->model->loadAdmin('Elements');

        $t_item = $this->gadget->template->load('LayoutManager.html');
        $t_item->SetBlock('working_notification');
        $t_item->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $working_box = $t_item->ParseBlock('working_notification');
        $t_item->Blocks['working_notification']->Parsed = '';

        $t_item->SetBlock('msgbox-wrapper');
        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            foreach ($responses as $msg_id => $response) {
                $t_item->SetBlock('msgbox-wrapper/msgbox');
                $t_item->SetVariable('text', $response['text']);
                $t_item->SetVariable('type', $response['type']);
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
        $t_item->SetVariable('confirmDelete',    _t('LAYOUT_CONFIRM_DELETE'));
        $dragdrop = $t_item->ParseBlock('drag_drop');
        $t_item->Blocks['drag_drop']->Parsed = '';

        // Init layout
        $GLOBALS['app']->InstanceLayout();

        $fakeLayout = new Jaws_Layout();
        $fakeLayout->Load();
        $fakeLayout->AddScriptLink('libraries/mootools/core.js');
        $fakeLayout->AddScriptLink('libraries/mootools/more.js');
        $fakeLayout->AddScriptLink('include/Jaws/Resources/Ajax.js');
        $fakeLayout->AddScriptLink('gadgets/Layout/Resources/script.js');

        $layoutContent = $fakeLayout->_Template->Blocks['layout']->Content;
        $layoutContent = preg_replace(
            '$<body([^>]*)>$i',
            '<body\1>' . $working_box . $msg_box . $this->getLayoutControls(),
            $layoutContent);
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
            $gadgets = $lModel->GetGadgetsInSection($name);
            if (!is_array($gadgets)) {
                continue;
            }

            foreach ($gadgets as $gadget) {
                if ($gadget['gadget'] == '[REQUESTEDGADGET]') {
                    $t_item->SetBlock('item');
                    $t_item->SetVariable('section_id', $name);
                    $t_item->SetVariable('item_id', $gadget['id']);
                    $t_item->SetVariable('pos', $gadget['layout_position']);
                    $t_item->SetVariable('gadget', _t('LAYOUT_REQUESTED_GADGET'));
                    $t_item->SetVariable('action', '&nbsp;');
                    $t_item->SetVariable('icon', 'gadgets/Layout/Resources/images/requested-gadget.png');
                    $t_item->SetVariable('description', _t('LAYOUT_REQUESTED_GADGET_DESC'));
                    $t_item->SetVariable('lbl_display_when', _t('LAYOUT_DISPLAY_IN'));
                    $t_item->SetVariable('display_when', _t('GLOBAL_ALWAYS'));
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
                    $t_item->SetVariable('pos', $gadget['layout_position']);
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
                    if (isset($actions[$gadget['gadget_action']]) &&
                        Jaws_Gadget::IsGadgetEnabled($gadget['gadget'])
                    ) {
                        $t_item->SetVariable('gadget', _t(strtoupper($gadget['gadget']).'_TITLE'));
                        if (isset($actions[$gadget['gadget_action']]['name'])) {
                            $t_item->SetVariable('action', $actions[$gadget['gadget_action']]['name']);
                        } else {
                            $t_item->SetVariable('action', $gadget['gadget_action']);
                        }
                        $t_item->SetVariable('description', $actions[$gadget['gadget_action']]['desc']);
                        $t_item->SetVariable('item_status', 'none');
                    } else {
                        $t_item->SetVariable('gadget', $gadget['gadget']);
                        $t_item->SetVariable('action', $gadget['gadget_action']);
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

            $fakeLayout->_Template->SetVariable(
                'ELEMENT', '<div id="layout_'.$name.'" class="layout-section" title="'.
                $name.'">'.$js_section_array.$t_item->Get().'</div>');

            $fakeLayout->_Template->ParseBlock('layout/'.$name);
            $t_item->Blocks['item']->Parsed = '';
        }

        return $fakeLayout->Get(true);
    }

    /**
     *
     *
     */
    function getLayoutControls()
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

        // layouts/dashboards
        if ($GLOBALS['app']->Session->GetPermission('Users', 'ManageDashboard')) {
            $tpl->SetBlock('controls/layouts');
            $tpl->SetVariable('layouts', _t('LAYOUT_LAYOUTS'));
            $layoutsCombo =& Piwi::CreateWidget('Combo', 'layouts');
            $layoutsCombo->setID('layouts');
            if ($this->gadget->GetPermission('ManageLayout')) {
                $layoutsCombo->AddOption(_t('LAYOUT_LAYOUTS_GLOBAL'), '0');
            }
            $layoutsCombo->AddOption(_t('LAYOUT_LAYOUTS_USER'), $GLOBALS['app']->Session->GetAttribute('user'));
            $layoutsCombo->SetDefault($GLOBALS['app']->Session->GetAttribute('layout'));
            $layoutsCombo->AddEvent(ON_CHANGE, "this.form.submit();");
            $tpl->SetVariable('layouts_combo', $layoutsCombo->Get());
            $tpl->ParseBlock('controls/layouts');
        }

        // themes
        $tpl->SetVariable('theme', _t('LAYOUT_THEME'));
        $themeCombo =& Piwi::CreateWidget('ComboGroup', 'theme');
        $themeCombo->setID('theme');
        $themeCombo->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
        $themeCombo->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $themes = Jaws_Utils::GetThemesList();
        foreach ($themes as $theme => $tInfo) {
            $themeCombo->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
        }
        $themeCombo->SetDefault(
            $this->gadget->registry->fetchByUser(
                'theme',
                'Settings',
                $GLOBALS['app']->Session->GetAttribute('layout')
            )
        );
        $themeCombo->AddEvent(ON_CHANGE, "this.form.submit();");
        $themeCombo->SetEnabled($this->gadget->GetPermission('ManageThemes'));
        $tpl->SetVariable('theme_combo', $themeCombo->Get());

        $add =& Piwi::CreateWidget('Button', 'add', _t('LAYOUT_NEW'), STOCK_ADD);
        $url = $GLOBALS['app']->getSiteURL().'/'.BASE_SCRIPT.'?gadget=Layout&amp;action=AddLayoutElement&amp;mode=new';
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

}