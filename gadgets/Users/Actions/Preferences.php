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
class Users_Actions_Preferences extends Jaws_Gadget_Action
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
        $this->AjaxMe('index.js');

        // Load the template
        $tpl = $this->gadget->template->load('Preferences.html');
        $tpl->SetBlock('preferences');
        $tpl->SetVariable('title', _t('USERS_PREFERENCES_INFO'));

        $gDir = JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets  = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $gInfo) {
            if (!file_exists($gDir . $gadget. '/Hooks/Preferences.php')) {
                continue;
            }

            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                continue;
            }

            $objHook = $objGadget->hook->load('Preferences');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $options = $objHook->Execute();
            if (Jaws_Error::IsError($options)) {
                continue;
            }

            $keys = $GLOBALS['app']->Registry->fetchAll('Settings', true);
            $customized = $this->gadget->registry->fetchAllByUser($gadget);
            $customized = array_column($customized, 'key_value', 'key_name');

            $tpl->SetBlock('preferences/gadget');
            $tpl->SetVariable('component', $gadget);
            $tpl->SetVariable('lbl_component', $objGadget->title);
            foreach ($keys as $key_name => $key_value) {
                $tpl->SetBlock('preferences/gadget/key');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->SetVariable('key_name', $key_name);
                if (@isset($options[$key_name]['values'])) {
                    $element =& Piwi::CreateWidget('Combo', $key_name);
                    $element->SetID($key_name);
                    $element->AddOption(_t('USERS_ADVANCED_OPTS_NOT_YET'), '');
                    foreach ($options[$key_name]['values'] as $value => $title) {
                        $element->AddOption($title, $value);
                    }
                } else {
                    $element =& Piwi::CreateWidget('Entry', $key_name);
                    $element->SetID($key_name);
                }

                $element->SetValue(isset($customized[$key_name])? $customized[$key_name] : '');
                $tpl->SetVariable('input_value', $element->Get());

                $tpl->ParseBlock('preferences/gadget/key');
            }
            $tpl->SetVariable('update', _t('GLOBAL_UPDATE'));
            $tpl->ParseBlock('preferences/gadget');
        }

        if ($response = $GLOBALS['app']->Session->PopResponse('Users.Preferences')) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
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
        /**
         * determine value isn't set?
         *
         * @access  private
         * @param   mixed   $option
         * @return  bool
         */
        function definedFilter($option) {
            return $option !== '';
        }

        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        // check permission
        $this->gadget->CheckPermission('EditUserPreferences');
        $post = jaws()->request->fetchAll('post');
        $gadget = $post['component'];
        unset($post['gadget'], $post['action'], $post['component']);
        // filter defined options
        $post = array_filter($post, 'definedFilter');
        $this->gadget->registry->deleteByUser($gadget);
        $result = $this->gadget->registry->insertAllByUser(
            array_map(null, array_keys($post), array_values($post)),
            $gadget
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

        Jaws_Header::Location($this->gadget->urlMap('Preferences'), 'Users.Preferences');
    }

}