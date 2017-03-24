<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Properties extends Users_Actions_Admin_Default
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->gadget->CheckPermission('ManageProperties');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        // authentication method
        $authtype =& Piwi::CreateWidget('Combo', 'authtype');
        foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
            $authtype->AddOption($method, $method);
        }
        $authtype->SetDefault($this->gadget->registry->fetch('authtype'));
        $authtype->SetEnabled($this->gadget->GetPermission('ManageAuthenticationMethod'));
        $tpl->SetVariable('lbl_authtype', _t('GLOBAL_AUTHTYPE'));
        $tpl->SetVariable('authtype', $authtype->Get());

        $anonRegister =& Piwi::CreateWidget('Combo', 'anon_register');
        $anonRegister->AddOption(_t('GLOBAL_YES'), 'true');
        $anonRegister->AddOption(_t('GLOBAL_NO'), 'false');
        $anonRegister->SetDefault($this->gadget->registry->fetch('anon_register'));
        $tpl->SetVariable('lbl_anon_register', _t('USERS_PROPERTIES_ANON_REGISTER'));
        $tpl->SetVariable('anon_register', $anonRegister->Get());

        $anonactivate =& Piwi::CreateWidget('Combo', 'anon_activation');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_AUTO'), 'auto');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_USER'), 'user');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_ADMIN'), 'admin');
        $anonactivate->SetDefault($this->gadget->registry->fetch('anon_activation'));
        $tpl->SetVariable('lbl_anon_activation', _t('USERS_PROPERTIES_ANON_ACTIVATION'));
        $tpl->SetVariable('anon_activation', $anonactivate->Get());

        $anonGroup =& Piwi::CreateWidget('Combo', 'anon_group');
        $anonGroup->SetID('anon_group');
        $anonGroup->AddOption(_t('USERS_GROUPS_NOGROUP'), 0);
        $groups = jaws()->loadObject('Jaws_User', 'Users')->GetGroups(null, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $anonGroup->AddOption($group['title'], $group['id']);
            }
        }
        $anonGroup->SetDefault($this->gadget->registry->fetch('anon_group'));
        $tpl->SetVariable('lbl_anon_group', _t('USERS_PROPERTIES_ANON_GROUP'));
        $tpl->SetVariable('anon_group', $anonGroup->Get());

        $passRecovery =& Piwi::CreateWidget('Combo', 'password_recovery');
        $passRecovery->AddOption(_t('GLOBAL_YES'), 'true');
        $passRecovery->AddOption(_t('GLOBAL_NO'), 'false');
        $passRecovery->SetDefault($this->gadget->registry->fetch('password_recovery'));
        $tpl->SetVariable('lbl_password_recovery', _t('USERS_PROPERTIES_PASS_RECOVERY'));
        $tpl->SetVariable('password_recovery', $passRecovery->Get());

        // reserved users
        $reservedUsers =& Piwi::CreateWidget(
            'TextArea',
            'reserved_users',
            trim($this->gadget->registry->fetch('reserved_users'))
        );
        $reservedUsers->SetRows(8);
        $reservedUsers->setID('reserved_users');
        $tpl->SetVariable('lbl_reserved_users', _t('USERS_PROPERTIES_RESERVED_USERS'));
        $tpl->SetVariable('reserved_users', $reservedUsers->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveSettings();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}