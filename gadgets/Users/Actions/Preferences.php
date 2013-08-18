<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Preferences extends Users_HTML
{
    /**
     * Prepares a simple form to update user's data (name, email, password)
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Preferences()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPreferences');
        //Here we load the Settings/Layout models (which is part of core) to extract some data
        $settingsModel = $GLOBALS['app']->loadGadget('Settings', 'AdminModel');

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser = new Jaws_User;
        $info  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), false, false, true);

        // Load the template
        $tpl = $this->gadget->loadTemplate('Preferences.html');
        $tpl->SetBlock('preferences');
        
        $tpl->SetVariable('title', _t('USERS_PREFERENCES_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

        // avatar
        if (empty($info['avatar'])) {
            $user_current_avatar = $GLOBALS['app']->getSiteURL('/gadgets/Users/images/photo128px.png');
        } else {
            $user_current_avatar = $GLOBALS['app']->getDataURL() . "avatar/" . $info['avatar'];
            $user_current_avatar .= !empty($info['last_update']) ? "?" . $info['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        //Language
        $lang =& Piwi::CreateWidget('Combo', 'user_language');
        $lang->setID('user_language');
        $lang->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), null);
        $languages = Jaws_Utils::GetLanguagesList();
        foreach($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($info['language']);
        $lang->SetTitle(_t('USERS_ADVANCED_OPTS_LANGUAGE'));
        $tpl->SetVariable('user_language', $lang->Get());
        $tpl->SetVariable('language', _t('USERS_ADVANCED_OPTS_LANGUAGE'));

        //Theme
        $uTheme =& Piwi::CreateWidget('ComboGroup', 'user_theme');
        $uTheme->setID('user_theme');
        $uTheme->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
        $uTheme->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $uTheme->AddOption('local', _t('USERS_ADVANCED_OPTS_NOT_YET'), null);
        $themes = Jaws_Utils::GetThemesList();
        foreach ($themes as $theme => $tInfo) {
            $uTheme->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
        }
        $uTheme->SetDefault($info['theme']);
        $uTheme->SetTitle(_t('USERS_ADVANCED_OPTS_THEME'));
        $tpl->SetVariable('user_theme', $uTheme->Get());
        $tpl->SetVariable('theme', _t('USERS_ADVANCED_OPTS_THEME'));

        //Editor
        $editor =& Piwi::CreateWidget('Combo', 'user_editor');
        $editor->setID('user_editor');
        $editor->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), null);
        $editors = $settingsModel->GetEditorList();
        foreach($editors as $k => $v) {
            $editor->AddOption($v, $k);
        }
        $editor->SetDefault($info['editor']);
        $editor->SetTitle(_t('USERS_ADVANCED_OPTS_EDITOR'));
        $tpl->SetVariable('user_editor', $editor->Get());
        $tpl->SetVariable('editor', _t('USERS_ADVANCED_OPTS_EDITOR'));

        //Time Zones
        $timezone =& Piwi::CreateWidget('Combo', 'user_timezone');
        $timezone->setID('user_timezone');
        $timezone->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), null);
        $timezones = $settingsModel->GetTimeZonesList();
        foreach($timezones as $k => $v) {
            $timezone->AddOption($v, $k);
        }
        $timezone->SetDefault($info['timezone']);
        $timezone->SetTitle(_t('GLOBAL_TIMEZONE'));
        $tpl->SetVariable('user_timezone', $timezone->Get());
        $tpl->SetVariable('timezone', _t('GLOBAL_TIMEZONE'));

        if ($response = $GLOBALS['app']->Session->PopResponse('Users.Preferences')) {
            $tpl->SetBlock('preferences/response');
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('preferences/response');
        }
        $tpl->ParseBlock('preferences');
        return $tpl->Get();
    }

    /**
     * Updates user information
     *
     * @access  public
     * @return  void
     */
    function UpdatePreferences()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPreferences');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('user_language', 'user_theme', 'user_editor', 'user_timezone'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Users', 'Model', 'Preferences');
        $result = $model->UpdatePreferences(
            $GLOBALS['app']->Session->GetAttribute('user'),
            $post['user_language'],
            $post['user_theme'],
            $post['user_editor'],
            $post['user_timezone']
        );
        if (!Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('USERS_PREFERENCES_UPDATED'),
                'Users.Preferences'
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                $result->GetMessage(),
                'Users.Preferences',
                RESPONSE_ERROR
            );
        }

        Jaws_Header::Location($this->gadget->urlMap('Preferences'));
    }

}