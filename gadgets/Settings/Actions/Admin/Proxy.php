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
class Settings_Actions_Admin_Proxy extends Settings_Actions_Admin_Default
{
    /**
     * Displays general/proxy settings form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ProxySettings()
    {
        $this->gadget->CheckPermission('ProxySettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Proxy'));
        $tpl->SetVariable('legend', $this::t('PROXY_SETTINGS'));

        $saveButton =& Piwi::CreateWidget('Button', 'save', Jaws::t('SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Settings').submitProxySettingsForm();");
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Use Enabled?
        $useProxy =& Piwi::CreateWidget('Combo', 'proxy_enabled');
        $useProxy->setID('proxy_enabled');
        $useProxy->AddOption(Jaws::t('NOO'),  'false');
        $useProxy->AddOption(Jaws::t('YESS'), 'true');
        $useProxy->SetDefault($this->gadget->registry->fetch('proxy_enabled'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'proxy_enabled');
        $tpl->SetVariable('label', Jaws::t('ENABLED'));
        $tpl->SetVariable('field', $useProxy->Get());
        $tpl->SetVariable('style', 'padding-bottom: 8px;');
        $tpl->ParseBlock('settings/item');

        // Proxy Host
        $tpl->SetBlock('settings/item');
        $proxyHost =& Piwi::CreateWidget('Entry', 'proxy_host', $this->gadget->registry->fetch('proxy_host'));
        $proxyHost->setID('proxy_host');
        $proxyHost->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'proxy_host');
        $tpl->SetVariable('label', $this::t('PROXY_HOST'));
        $tpl->SetVariable('field', $proxyHost->Get());
        $tpl->SetVariable('style', 'padding-bottom: 0px;');
        $tpl->ParseBlock('settings/item');

        // Proxy Port
        $tpl->SetBlock('settings/item');
        $proxyPort =& Piwi::CreateWidget('Entry', 'proxy_port', $this->gadget->registry->fetch('proxy_port'));
        $proxyPort->setID('proxy_port');
        $proxyPort->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'proxy_port');
        $tpl->SetVariable('label', $this::t('PROXY_PORT'));
        $tpl->SetVariable('field', $proxyPort->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Auth
        $proxyAuth =& Piwi::CreateWidget('Combo', 'proxy_auth');
        $proxyAuth->setID('proxy_auth');
        $proxyAuth->AddOption(Jaws::t('NOO'),  'false');
        $proxyAuth->AddOption(Jaws::t('YESS'), 'true');
        $proxyAuth->SetDefault($this->gadget->registry->fetch('proxy_auth'));
        $tpl->SetBlock('settings/item');
        $tpl->SetVariable('field-name', 'proxy_auth');
        $tpl->SetVariable('label', $this::t('PROXY_AUTH'));
        $tpl->SetVariable('field', $proxyAuth->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Username
        $tpl->SetBlock('settings/item');
        $proxyUser =& Piwi::CreateWidget('Entry', 'proxy_user', $this->gadget->registry->fetch('proxy_user'));
        $proxyUser->setID('proxy_user');
        $proxyUser->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'proxy_user');
        $tpl->SetVariable('label', $this::t('PROXY_USER'));
        $tpl->SetVariable('field', $proxyUser->Get());
        $tpl->ParseBlock('settings/item');

        // Proxy Password
        $tpl->SetBlock('settings/item');
        $proxyPass =& Piwi::CreateWidget('PasswordEntry', 'proxy_pass', '');
        $proxyPass->setID('proxy_pass');
        $proxyPass->setStyle('direction:ltr');
        $tpl->SetVariable('field-name', 'proxy_pass');
        $tpl->SetVariable('label', $this::t('PROXY_PASS'));
        $tpl->SetVariable('field', $proxyPass->Get());
        $tpl->ParseBlock('settings/item');

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}