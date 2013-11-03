<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
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
            $sidebar->AddOption('Basic', _t('SETTINGS_BASIC_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=BasicSettings');
        }

        if ($this->gadget->GetPermission('AdvancedSettings')) {
            $sidebar->AddOption('Advanced', _t('SETTINGS_ADVANCED_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=AdvancedSettings');
        }

        if ($this->gadget->GetPermission('MetaSettings')) {
            $sidebar->AddOption('Meta', _t('SETTINGS_META_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=MetaSettings');
        }

        if ($this->gadget->GetPermission('MailSettings')) {
            $sidebar->AddOption('Mail', _t('SETTINGS_MAIL_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=MailSettings');
        }

        if ($this->gadget->GetPermission('FTPSettings')) {
            $sidebar->AddOption('FTP', _t('SETTINGS_FTP_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=FTPSettings');
        }

        if ($this->gadget->GetPermission('ProxySettings')) {
            $sidebar->AddOption('Proxy', _t('SETTINGS_PROXY_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Settings&amp;action=ProxySettings');
        }

        $sidebar->Activate($action);
        return $sidebar->Get();
    }

}