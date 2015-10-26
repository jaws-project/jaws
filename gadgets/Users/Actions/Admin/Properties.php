<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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

        $tpl = $this->gadget->template->loadAdmin('Properties.html');
        $tpl->SetBlock('Properties');

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Users'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveProperties'));

        $authtype =& Piwi::CreateWidget('Combo', 'authtype');
        $authtype->SetTitle(_t('GLOBAL_AUTHTYPE'));
        foreach ($GLOBALS['app']->GetAuthTypes() as $method) {
            $authtype->AddOption($method, $method);
        }
        $authtype->SetDefault($this->gadget->registry->fetch('authtype'));
        $authtype->SetEnabled($this->gadget->CheckPermission('ManageAuthenticationMethod'));

        $anonRegister =& Piwi::CreateWidget('Combo', 'anon_register');
        $anonRegister->SetTitle(_t('USERS_PROPERTIES_ANON_REGISTER'));
        $anonRegister->AddOption(_t('GLOBAL_YES'), 'true');
        $anonRegister->AddOption(_t('GLOBAL_NO'), 'false');
        $anonRegister->SetDefault($this->gadget->registry->fetch('anon_register'));

        $anonactivate =& Piwi::CreateWidget('Combo', 'anon_activation');
        $anonactivate->SetTitle(_t('USERS_PROPERTIES_ANON_ACTIVATION'));
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_AUTO'), 'auto');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_USER'), 'user');
        $anonactivate->AddOption(_t('USERS_PROPERTIES_ACTIVATION_BY_ADMIN'), 'admin');
        $anonactivate->SetDefault($this->gadget->registry->fetch('anon_activation'));

        $userModel = new Jaws_User();
        $anonGroup =& Piwi::CreateWidget('Combo', 'anon_group');
        $anonGroup->SetID('anon_group');
        $anonGroup->SetTitle(_t('USERS_PROPERTIES_ANON_GROUP'));
        $anonGroup->AddOption(_t('USERS_GROUPS_NOGROUP'), 0);
        $groups = $userModel->GetGroups(null, 'title');
        if (!Jaws_Error::IsError($groups)) {
            foreach ($groups as $group) {
                $anonGroup->AddOption($group['title'], $group['id']);
            }
        }
        $anonGroup->SetDefault($this->gadget->registry->fetch('anon_group'));

        $passRecovery =& Piwi::CreateWidget('Combo', 'password_recovery');
        $passRecovery->SetTitle(_t('USERS_PROPERTIES_PASS_RECOVERY'));
        $passRecovery->AddOption(_t('GLOBAL_YES'), 'true');
        $passRecovery->AddOption(_t('GLOBAL_NO'), 'false');
        $passRecovery->SetDefault($this->gadget->registry->fetch('password_recovery'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet('');
        $fieldset->SetTitle('vertical');

        $fieldset->Add($authtype);
        $fieldset->Add($anonRegister);
        $fieldset->Add($anonactivate);
        $fieldset->Add($anonGroup);
        $fieldset->Add($passRecovery);

        $form->Add($fieldset);

        $buttons =& Piwi::CreateWidget('HBox');
        $buttons->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');

        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:saveSettings();');

        $buttons->Add($save);
        $form->Add($buttons);

        $tpl->SetVariable('form', $form->Get());
        $tpl->SetVariable('menubar', $this->MenuBar('Properties'));

        $tpl->ParseBlock('Properties');

        return $tpl->Get();
    }

}