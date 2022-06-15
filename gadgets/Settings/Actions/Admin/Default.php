<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the settings sidebar
     *
     * @access  private
     * @param   string  $action Current action
     * @return  string  XHTML sidebar
     */
    function SideBar($action)
    {
        $actions = array('Basic', 'Advanced', 'Meta', 'Mail', 'FTP', 'Proxy');
        if (!in_array($action, $actions)) {
            $action = 'Basic';
        }

        $sidebar = new Jaws_Widgets_Sidebar('settings');
        if ($this->gadget->GetPermission('BasicSettings')) {
            $sidebar->AddOption('Basic', $this::t('BASIC_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=BasicSettings');
        }

        if ($this->gadget->GetPermission('AdvancedSettings')) {
            $sidebar->AddOption('Advanced', $this::t('ADVANCED_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=AdvancedSettings');
        }

        if ($this->gadget->GetPermission('MetaSettings')) {
            $sidebar->AddOption('Meta', $this::t('META_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=MetaSettings');
        }

        if ($this->gadget->GetPermission('MailSettings')) {
            $sidebar->AddOption('Mail', $this::t('MAIL_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=MailSettings');
        }

        if ($this->gadget->GetPermission('FTPSettings')) {
            $sidebar->AddOption('FTP', $this::t('FTP_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=FTPSettings');
        }

        if ($this->gadget->GetPermission('ProxySettings')) {
            $sidebar->AddOption('Proxy', $this::t('PROXY_SETTINGS'),
                                BASE_SCRIPT . '?reqGadget=Settings&amp;reqAction=ProxySettings');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }

}