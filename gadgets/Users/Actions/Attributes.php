<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Attributes extends Users_Actions_Default
{
    /**
     * Prepares a simple form to update user's custom attributes
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function UserAttributes()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $gadget = $this->gadget->request->fetch('gadget', 'get');
        $gadget = preg_replace('/[^[:alnum:]_]/', '', (string)$gadget);
        if (empty($gadget)) {
            return Jaws_HTTPError::Get(404);
        }

        // find real gadget name
        $gPath = ROOT_JAWS_PATH. 'gadgets/';
        $hooks = glob($gPath . '*/Hooks/UserAttributes.php');
        $gadgets = preg_replace(
            '@'.preg_quote($gPath, '@'). '(\w*)/Hooks/UserAttributes.php@',
            '${1}',
            $hooks
        );
        if (false === $indx = array_search(strtolower($gadget), array_map('strtolower', $gadgets))) {
            return Jaws_HTTPError::Get(404);
        }
        $gadget = $gadgets[$indx];

        $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('UserAttributes');
        if (Jaws_Error::IsError($objHook)) {
            return Jaws_HTTPError::Get(404);
        }

        $attrs = $objHook->Execute();
        if (Jaws_Error::IsError($options) || empty($attrs)) {
            return Jaws_HTTPError::Get(500);
        }

        // check access to edit this gadget custom attributes
        $objHook->gadget->CheckPermission('ModifyUserAttributes');

        // fetch user custom attributes
        $attrValues = $objHook->gadget->users->fetch(
            $this->app->session->user->id,
            array('custom' => array_keys($attrs)),
            'inner'
        );
        if (Jaws_Error::IsError($result)) {
            return Jaws_HTTPError::Get(500);
        }

        // load js files
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('UserAttributes.html');
        $tpl->SetBlock('attributes');
        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('title', Jaws_Gadget::t("$gadget.TITLE"));
        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        // load attributes interface
        $this->interfaceAttributes($tpl, $attrs, $attrValues);

        $tpl->SetVariable('update', Jaws::t('UPDATE'));
        if ($response = $this->gadget->session->pop('UserAttributes')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('attributes');
        return $tpl->Get();
    }

    /**
     * Updates user information
     *
     * @access  public
     * @return  void
     */
    function UpdateUserAttributes()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        // fetch all posted data
        $postedData = $this->gadget->request->fetchAll('post');

        $objHook = Jaws_Gadget::getInstance($postedData['gadget'])->hook->load('UserAttributes');
        if (Jaws_Error::IsError($objHook)) {
            return Jaws_HTTPError::Get(404);
        }

        // get custom user's attributes 
        $attrs = $objHook->Execute();
        if (Jaws_Error::IsError($options) || empty($attrs)) {
            return Jaws_HTTPError::Get(500);
        }

        // check access to edit this gadget custom attributes
        $objHook->gadget->CheckPermission('ModifyUserAttributes');

        // remove invalid attributes
        $inputAttrs = array_intersect_key($postedData, $attrs);

        try {
            // validate attributes
            $this->validateAttributes($attrs, $inputAttrs);

            // update user's custom attributes
            $result = $objHook->gadget->users->upsertAttributes(
                $this->app->session->user->id,
                $inputAttrs
            );
            if (Jaws_Error::IsError($result)) {
                throw new Exception($result->GetMessage(), 500);
            }

            $this->gadget->session->push(
                $this::t('ATTRIBUTES_UPDATED'),
                RESPONSE_NOTICE,
                'UserAttributes'
            );
        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                RESPONSE_ERROR,
                'UserAttributes'
            );
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('UserAttributes', array('gadget' => $objHook->gadget->name)),
            'UserAttributes'
        );
    }

    /**
     * Prepares a simple form to update group's custom attributes
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function GroupAttributes()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $gadget = $this->gadget->request->fetch('gadget', 'get');
        $gadget = preg_replace('/[^[:alnum:]_]/', '', (string)$gadget);
        if (empty($gadget)) {
            return Jaws_HTTPError::Get(404);
        }

        // find real gadget name
        $gPath = ROOT_JAWS_PATH. 'gadgets/';
        $hooks = glob($gPath . '*/Hooks/GroupAttributes.php');
        $gadgets = preg_replace(
            '@'.preg_quote($gPath, '@'). '(\w*)/Hooks/GroupAttributes.php@',
            '${1}',
            $hooks
        );
        if (false === $indx = array_search(strtolower($gadget), array_map('strtolower', $gadgets))) {
            return Jaws_HTTPError::Get(404);
        }
        $gadget = $gadgets[$indx];

        $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('GroupAttributes');
        if (Jaws_Error::IsError($objHook)) {
            return Jaws_HTTPError::Get(404);
        }

        $attrs = $objHook->Execute();
        if (Jaws_Error::IsError($options) || empty($attrs)) {
            return Jaws_HTTPError::Get(500);
        }

        // check access to edit this gadget custom attributes
        $objHook->gadget->CheckPermission('ModifyGroupAttributes');

        $fetchedGroup = (int)$this->gadget->request->fetch('group', 'get');
        $group = (int)$objHook->AttributesGroup($fetchedGroup);
        if (empty($group)) {
            return Jaws_HTTPError::Get(404);
        }

        // fetch user custom attributes
        $attrValues = $objHook->gadget->groups->fetch(
            $group,
            array('custom' => array_keys($attrs)),
            'inner'
        );
        if (Jaws_Error::IsError($result)) {
            return Jaws_HTTPError::Get(500);
        }

        // load js files
        $this->AjaxMe('index.js');
        // Load the template
        $tpl = $this->gadget->template->load('GroupAttributes.html');
        $tpl->SetBlock('attributes');
        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('title', Jaws_Gadget::t("$gadget.TITLE"));
        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);
        // load attributes interface
        $this->interfaceAttributes($tpl, $attrs, $attrValues);

        $tpl->SetVariable('update', Jaws::t('UPDATE'));
        if ($response = $this->gadget->session->pop('GroupAttributes')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('attributes');
        return $tpl->Get();
    }

    /**
     * Updates user information
     *
     * @access  public
     * @return  void
     */
    function UpdateGroupAttributes()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        // fetch all posted data
        $postedData = $this->gadget->request->fetchAll('post');

        $objHook = Jaws_Gadget::getInstance($postedData['gadget'])->hook->load('GroupAttributes');
        if (Jaws_Error::IsError($objHook)) {
            return Jaws_HTTPError::Get(404);
        }

        // get custom user's attributes 
        $attrs = $objHook->Execute();
        if (Jaws_Error::IsError($options) || empty($attrs)) {
            return Jaws_HTTPError::Get(500);
        }

        // check access to edit this gadget custom attributes
        $objHook->gadget->CheckPermission('ModifyGroupAttributes');

        $fetchedGroup = (int)$this->gadget->request->fetch('group', 'get');
        $group = (int)$objHook->AttributesGroup($fetchedGroup);
        if (empty($group)) {
            return Jaws_HTTPError::Get(404);
        }

        // remove invalid attributes
        $inputAttrs = array_intersect_key($postedData, $attrs);

        try {
            // validate attributes
            $this->validateAttributes($attrs, $inputAttrs);

            // update user's custom attributes
            $result = $objHook->gadget->groups->upsertAttributes(
                $group,
                $inputAttrs
            );
            if (Jaws_Error::IsError($result)) {
                throw new Exception($result->GetMessage(), 500);
            }

            $this->gadget->session->push(
                $this::t('ATTRIBUTES_UPDATED'),
                RESPONSE_NOTICE,
                'GroupAttributes'
            );
        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                RESPONSE_ERROR,
                'GroupAttributes'
            );
        }

        $actionParams = array('gadget' => $objHook->gadget->name);
        if (!empty($fetchedGroup)) {
            // check "fetchedGroup" but must use "group" variable
            $actionParams['group'] = $group;
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('GroupAttributes', $actionParams),
            'GroupAttributes'
        );
    }

    /**
     * Generate attributes edit interface
     *
     * @access  public
     * @param   object  $tpl    Jaws Template object
     * @param   array   $attrs  Attributes options
     * @param   array   $values Attributes values
     * @return  string  XHTML template of a form
     */
    function interfaceAttributes(&$tpl, $attrs, $attrValues)
    {
        foreach ($attrs as $attrName => $attrOptions) {
            // set default value
            $defaultValue = isset($attrOptions['value'])? $attrOptions['value'] : null;
            if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                $defaultValue = $attrValues[$attrName];
            }

            // set attribute block name
            $blockName = $attrOptions['type'];
            if (isset($attrOptions['hidden']) && $attrOptions['hidden']) {
                $blockName = 'hidden';
            } else {
                $attrOptions['hidden'] = false;
            }

            $tpl->SetBlock('attributes/attribute');
            if ($attrOptions['hidden']) {
                $tpl->SetBlock('attributes/attribute/hide');
                $tpl->ParseBlock('attributes/attribute/hide');
            }

            $tpl->SetBlock('attributes/attribute/'. $blockName);
            $tpl->SetVariable('attribute_title', $attrOptions['title']);
            $tpl->SetVariable('attribute_name', $attrName);
            $tpl->SetVariable('attribute_type', $attrOptions['type']);
            $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
            $tpl->SetVariable('value', $defaultValue);

            // required
            $tpl->SetVariable('required', '');
            if (isset($attrOptions['required']) && $attrOptions['required']) {
                $tpl->SetVariable('required', 'required');
            }
            // disabled
            $tpl->SetVariable('disabled', '');
            if (isset($attrOptions['disabled']) && $attrOptions['disabled']) {
                $tpl->SetVariable('disabled', 'disabled');
            }
            // readonly
            $tpl->SetVariable('readonly', '');
            if (isset($attrOptions['readonly']) && $attrOptions['readonly']) {
                $tpl->SetVariable('readonly', 'readonly');
            }
            // placeholder
            $tpl->SetVariable('placeholder', '');
            if (isset($attrOptions['placeholder'])) {
                $tpl->SetVariable('placeholder', $attrOptions['placeholder']);
            }

            switch ($attrOptions['type']) {
                case 'select':
                    foreach ($attrOptions['values'] as $value => $text) {
                        $tpl->SetBlock('attributes/attribute/select/option');
                        $tpl->SetVariable('value', $value);
                        $tpl->SetVariable('text', $text);
                        if ($defaultValue == $value) {
                            $tpl->SetBlock('attributes/attribute/select/option/selected');
                            $tpl->ParseBlock('attributes/attribute/select/option/selected');
                        }
                        $tpl->ParseBlock('attributes/attribute/select/option');
                    }

                    break;

                case 'user':
                    break;

                case 'checkbox':
                    if (!empty($defaultValue)) {
                        $tpl->SetBlock('attributes/attribute/checkbox/checked');
                        $tpl->ParseBlock('attributes/attribute/checkbox/checked');
                    }
                    break;

                case 'number':
                    $tpl->SetVariable('min', '');
                    $tpl->SetVariable('max', '');
                    // set value
                    if (!is_null($defaultValue)) {
                        $tpl->SetVariable('value', $defaultValue);
                    }

                    if (isset($attrOptions['min'])) {
                        $tpl->SetVariable('min', $attrOptions['min']);
                    }
                    if (isset($attrOptions['max'])) {
                        $tpl->SetVariable('max', $attrOptions['max']);
                    }

                    break;

                case 'date':
                    $this->gadget->action->load('DatePicker')->calendar(
                        $tpl,
                        array(
                            'name' => $attrName,
                            'value' => isset($defaultValue)?
                                Jaws_Date::getInstance()->Format($defaultValue, 'Y/m/d') :
                                '',
                            'required' => isset($attrOptions['required'])? $attrOptions['required'] : false,
                            'readonly' => isset($attrOptions['readonly'])? $attrOptions['readonly'] : false,
                            'disabled' => isset($attrOptions['disabled'])? $attrOptions['disabled'] : false,
                        )
                    );
                    break;

                case 'country':
                    try {
                        $this->selectedCountry = $defaultValue;
                        if ($attrOptions['hidden']) {
                            throw new Exception('');
                        }

                        $countries = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCountries();
                        if (Jaws_Error::IsError($countries) || empty($countries)) {
                            throw new Exception('');
                        }

                        foreach ($countries as $country) {
                            $tpl->SetBlock('attributes/attribute/country/option');
                            $tpl->SetVariable('value', $country['country']);
                            $tpl->SetVariable('text', $country['title']);
                            if ($defaultValue == $country['country']) {
                                $tpl->SetBlock('attributes/attribute/country/option/selected');
                                $tpl->ParseBlock('attributes/attribute/country/option/selected');
                            }
                            $tpl->ParseBlock('attributes/attribute/country/option');
                        }
                    } catch (Exception $error) {
                        // don nothing
                    }

                    break;

                case 'province':
                    try {
                        $this->selectedProvince = $defaultValue;
                        if (empty($this->selectedCountry)) {
                            throw new Exception('');
                        }

                        $provinces = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetProvinces(
                            $this->selectedCountry
                        );
                        if (Jaws_Error::IsError($provinces) || empty($provinces)) {
                            throw new Exception('');
                        }

                        foreach ($provinces as $province) {
                            $tpl->SetBlock('attributes/attribute/province/option');
                            $tpl->SetVariable('value', $province['province']);
                            $tpl->SetVariable('text', $province['title']);
                            if ($defaultValue == $province['province']) {
                                $tpl->SetBlock('attributes/attribute/province/option/selected');
                                $tpl->ParseBlock('attributes/attribute/province/option/selected');
                            }
                            $tpl->ParseBlock('attributes/attribute/province/option');
                        }
                    } catch (Exception $error) {
                        // don nothing
                    }

                    break;

                case 'city':
                    try {
                        if (empty($this->selectedCountry) || empty($this->selectedProvince)) {
                            throw new Exception('');
                        }

                        $cities = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCities(
                            $this->selectedProvince,
                            $this->selectedCountry
                        );
                        if (Jaws_Error::IsError($cities) || empty($cities)) {
                            throw new Exception('');
                        }

                        // default value
                        $defaultValue = isset($attrOptions['value'])? $attrOptions['value'] : reset($cities)['city'];
                        if (!empty($attrValues) && !empty($attrValues[$attrName])) {
                            $defaultValue = $attrValues[$attrName];
                        }

                        foreach ($cities as $city) {
                            $tpl->SetBlock('attributes/attribute/city/option');
                            $tpl->SetVariable('value', $city['city']);
                            $tpl->SetVariable('text', $city['title']);
                            if ($defaultValue == $city['city']) {
                                $tpl->SetBlock('attributes/attribute/city/option/selected');
                                $tpl->ParseBlock('attributes/attribute/city/option/selected');
                            }
                            $tpl->ParseBlock('attributes/attribute/city/option');
                        }
                    } catch (Exception $error) {
                        // don nothing
                    }

                    break;

                default:
                    $tpl->SetVariable('minlength', '');
                    $tpl->SetVariable('maxlength', '');
                    // set value
                    if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                        $tpl->SetVariable('value', $attrValues[$attrName]);
                    } else {
                        $tpl->SetVariable('value', isset($attrOptions['value'])? $attrOptions['value'] : '');
                    }

                    if (isset($attrOptions['pattern'])) {
                        $tpl->SetBlock('attributes/attribute/text/pattern');
                        $tpl->SetVariable('pattern', $attrOptions['pattern']);
                        $tpl->ParseBlock('attributes/attribute/text/pattern');
                    }

                    if (isset($attrOptions['minlength'])) {
                        $tpl->SetVariable('minlength', $attrOptions['minlength']);
                    }
                    if (isset($attrOptions['maxlength'])) {
                        $tpl->SetVariable('maxlength', $attrOptions['maxlength']);
                    }

                    break;
            }

            $tpl->ParseBlock('attributes/attribute/'. $blockName);
            $tpl->ParseBlock('attributes/attribute');
        }

    }

    /**
     * Validate attributes values
     *
     * @access  public
     * @param   array   $attrs      Attributes options
     * @param   array   $inputAttrs Attributes values
     * @return  void
     */
    function validateAttributes(&$attrs, &$inputAttrs)
    {
        // typecast & validate input attributes value
        foreach ($attrs as $attrName => $attrOptions) {
            // unset read-only attribute
            if (isset($attrOptions['readonly']) && $attrOptions['readonly']) {
                unset($inputAttrs[$attrName]);
                continue;
            }

            if (isset($attrOptions['required']) && $attrOptions['required'] &&
                empty($inputAttrs[$attrName])
            ) {
                throw new Exception($this::t('FIELD_REQUIRED', $attrOptions['title']), 200);
                break;
            }

            switch ($attrOptions['type']) {
                case 'number':
                    $inputAttrs[$attrName] = (int)$inputAttrs[$attrName];
                    break;

                case 'checkbox':
                    $inputAttrs[$attrName] = array_key_exists($attrName, $inputAttrs);
                    break;

                case 'date':
                    if (!empty($inputAttrs[$attrName])) {
                        $tmpDate = Jaws_Date::getInstance()->ToBaseDate(
                            preg_split('/[\/\- :]/', $inputAttrs[$attrName])
                        );
                        $inputAttrs[$attrName] = $this->app->UserTime2UTC($tmpDate['timestamp']);
                    } else {
                        $inputAttrs[$attrName] = 0;
                    }
                    break;

                default:
                    // nothing
            }
        }
    }

}