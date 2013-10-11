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
        $settingsModel = $GLOBALS['app']->loadGadget('Settings', 'AdminModel', 'Settings');

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser = new Jaws_User;
        $info  = $jUser->GetUser($GLOBALS['app']->Session->GetAttribute('user'), false, false, true);

        // Load the template
        $tpl = $this->gadget->loadTemplate('Preferences.html');
        $tpl->SetBlock('preferences');

        $gDir = JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadgets  = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $gInfo) {
            if (!file_exists($gDir . $gadget. '/Hooks/Preferences.php')) {
                continue;
            }

            $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }

            $objHook = $objGadget->load('Hook')->load('Preferences');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $options = $objHook->Execute();
            if (Jaws_Error::IsError($options)) {
                continue;
            }

            $keys = $GLOBALS['app']->Registry->fetchAll('Settings', true);
            $keys = array_column($keys, 'key_value', 'key_name');
            $customized = $this->gadget->registry->fetchAllByUser($gadget);
            $customized = array_column($customized, 'key_value', 'key_name');

            $tpl->SetBlock('preferences/gadget');
            foreach ($keys as $key_name => $key_value) {
                $tpl->SetBlock('preferences/gadget/key');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->SetVariable('key_name', $key_name);
                $key_element_name = $gadget.'_'.$key_name;
                if (@isset($options[$key_name]['values'])) {
                    $element =& Piwi::CreateWidget('Combo', $key_element_name);
                    $element->SetID($key_element_name);
                    foreach ($options[$key_name]['values'] as $value => $title) {
                        $element->AddOption($title, $value);
                    }
                } else {
                    $element =& Piwi::CreateWidget('Entry', $key_element_name);
                    $element->SetID($key_element_name);
                }

                $element->SetValue(isset($customized[$key_name])? $customized[$key_name] : $key_value);
                $tpl->SetVariable('input_value', $element->Get());

                $tpl->ParseBlock('preferences/gadget/key');
            }
            $tpl->ParseBlock('preferences/gadget');
        }

        $tpl->SetVariable('title', _t('USERS_PREFERENCES_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', _t('USERS_USERS_ACCOUNT_UPDATE'));

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
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPreferences');
        $post = jaws()->request->fetch(array('user_language', 'user_theme', 'user_editor', 'user_timezone'), 'post');

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