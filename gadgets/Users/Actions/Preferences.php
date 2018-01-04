<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Preferences extends Users_Actions_Default
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
            return Jaws_Header::Location(
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

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

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

            $keys = $GLOBALS['app']->Registry->fetchAll($gadget, true);
            if (empty($keys)) {
                continue;
            }
            $customized = $this->gadget->registry->fetchAllByUser($gadget);

            $tpl->SetBlock('preferences/gadget');
            $tpl->SetVariable('component', $gadget);
            $tpl->SetVariable('lbl_component', $objGadget->title);
            foreach ($keys as $key_name => $key_value) {
                if (!array_key_exists($key_name, $options)) {
                    continue;
                }

                $options[$key_name]['type']  = @$options[$key_name]['type']?: 'text';
                $options[$key_name]['class'] = @$options[$key_name]['class']?: 'x-large';
                $customized[$key_name] = isset($customized[$key_name])? $customized[$key_name] : '';

                $tpl->SetBlock('preferences/gadget/key');
                $tpl->SetVariable('gadget', $gadget);
                $tpl->SetVariable('key_name', $key_name);
                $tpl->SetVariable('key_title', $options[$key_name]['title']);
                switch ($options[$key_name]['type']) {
                    case 'select':
                        $tpl->SetBlock('preferences/gadget/key/select');
                        $tpl->SetVariable('key_name', $key_name);
                        $tpl->SetVariable('class', $options[$key_name]['class']);

                        $options[$key_name]['values'] =
                            array('' => _t('USERS_ADVANCED_OPTS_NOT_YET')) +
                            $options[$key_name]['values'];
                        foreach ($options[$key_name]['values'] as $value => $text) {
                            $tpl->SetBlock('preferences/gadget/key/select/option');
                            $tpl->SetVariable('value', $value);
                            $tpl->SetVariable('text', $text);
                            if ($customized[$key_name] == $value) {
                                $tpl->SetBlock('preferences/gadget/key/select/option/selected');
                                $tpl->ParseBlock('preferences/gadget/key/select/option/selected');
                            }
                            $tpl->ParseBlock('preferences/gadget/key/select/option');
                        }

                        $tpl->ParseBlock('preferences/gadget/key/select');
                        break;

                    case 'checkbox':
                        $tpl->SetBlock('preferences/gadget/key/checkbox');
                        $tpl->SetVariable('key_name', $key_name);
                        $tpl->SetVariable('class', $options[$key_name]['class']);
                        if ($customized[$key_name]) {
                            $tpl->SetBlock('preferences/gadget/key/checkbox/checked');
                            $tpl->ParseBlock('preferences/gadget/key/checkbox/checked');
                        }
                        $tpl->ParseBlock('preferences/gadget/key/checkbox');
                        break;

                    case 'number':
                        $tpl->SetBlock('preferences/gadget/key/number');
                        $tpl->SetVariable('key_name', $key_name);
                        $tpl->SetVariable('value', $customized[$key_name]);
                        $tpl->SetVariable('class', $options[$key_name]['class']);
                        $tpl->ParseBlock('preferences/gadget/key/number');
                        break;

                    default:
                        $tpl->SetBlock('preferences/gadget/key/text');
                        $tpl->SetVariable('key_name', $key_name);
                        $tpl->SetVariable('value', $customized[$key_name]);
                        $tpl->SetVariable('class', $options[$key_name]['class']);
                        $tpl->ParseBlock('preferences/gadget/key/text');
                        break;
                }

                $tpl->ParseBlock('preferences/gadget/key');
            }
            $tpl->SetVariable('update', _t('GLOBAL_UPDATE'));
            $tpl->ParseBlock('preferences/gadget');
        }

        if ($response = $GLOBALS['app']->Session->PopResponse('Users.Preferences')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
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
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        // check permission
        $this->gadget->CheckPermission('EditUserPreferences');
        $post = $this->gadget->request->fetchAll('post');
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

        return Jaws_Header::Location($this->gadget->urlMap('Preferences'), 'Users.Preferences');
    }

}