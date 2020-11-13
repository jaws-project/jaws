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
    function Attributes()
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
        $gPath = ROOT_JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
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

        foreach ($attrs as $attrName => $attrOptions) {
            $tpl->SetBlock('attributes/attribute');

            $tpl->SetVariable('attribute_title', $attrOptions['title']);
            switch ($attrOptions['type']) {
                case 'select':
                    $tpl->SetBlock('attributes/attribute/select');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $tpl->SetVariable('required', '');
                    if (isset($attrOptions['required']) && $attrOptions['required']) {
                        $tpl->SetVariable('required', 'required');
                    }

                    foreach ($attrOptions['values'] as $value => $text) {
                        $tpl->SetBlock('attributes/attribute/select/option');
                        $tpl->SetVariable('value', $value);
                        $tpl->SetVariable('text', $text);
                        // select option
                        if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                            if ($attrValues[$attrName] == $value) {
                                $tpl->SetBlock('attributes/attribute/select/option/selected');
                                $tpl->ParseBlock('attributes/attribute/select/option/selected');
                            }
                        } elseif (isset($attrOptions['value'])) {
                            if ($attrOptions['value'] == $value) {
                                $tpl->SetBlock('attributes/attribute/select/option/selected');
                                $tpl->ParseBlock('attributes/attribute/select/option/selected');
                            }
                        }
                        $tpl->ParseBlock('attributes/attribute/select/option');
                    }

                    $tpl->ParseBlock('attributes/attribute/select');
                    break;

                case 'user':
                    $tpl->SetBlock('attributes/attribute/user');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $tpl->ParseBlock('attributes/attribute/user');
                    break;

                case 'checkbox':
                    $tpl->SetBlock('attributes/attribute/checkbox');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                        if (!empty($attrValues[$attrName])) {
                            $tpl->SetBlock('attributes/attribute/checkbox/checked');
                            $tpl->ParseBlock('attributes/attribute/checkbox/checked');
                        }
                    } elseif (isset($attrOptions['value'])) {
                        if ($attrOptions['value']) {
                            $tpl->SetBlock('attributes/attribute/checkbox/checked');
                            $tpl->ParseBlock('attributes/attribute/checkbox/checked');
                        }
                    }
                    $tpl->ParseBlock('attributes/attribute/checkbox');
                    break;

                case 'number':
                    $tpl->SetBlock('attributes/attribute/number');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $tpl->SetVariable('placeholder', '');
                    $tpl->SetVariable('required', '');
                    $tpl->SetVariable('min', '');
                    $tpl->SetVariable('max', '');
                    // set value
                    if (!empty($attrValues) && !is_null($attrValues[$attrName])) {
                        $tpl->SetVariable('value', $attrValues[$attrName]);
                    } else {
                        $tpl->SetVariable('value', isset($attrOptions['value'])? $attrOptions['value'] : '');
                    }

                    if (isset($attrOptions['required']) && $attrOptions['required']) {
                        $tpl->SetVariable('required', 'required');
                    }
                    if (isset($attrOptions['min'])) {
                        $tpl->SetVariable('min', $attrOptions['min']);
                    }
                    if (isset($attrOptions['max'])) {
                        $tpl->SetVariable('max', $attrOptions['max']);
                    }

                    if (isset($attrOptions['placeholder'])) {
                        $tpl->SetVariable('placeholder', $attrOptions['placeholder']);
                    }

                    $tpl->ParseBlock('attributes/attribute/number');
                    break;

                case 'date':
                    $tpl->SetBlock('attributes/attribute/date');
                    $required = false;
                    if (isset($attrOptions['required']) && $attrOptions['required']) {
                        $required = true;
                    }

                    $this->gadget->action->load('DatePicker')->calendar(
                        $tpl,
                        array(
                            'name' => $attrName,
                            'value' => isset($attrOptions['value']) ? $attrOptions['value'] : '',
                            'required' => $required
                        )
                    );

                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('value', isset($attrOptions['value'])? $attrOptions['value'] : '');
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $tpl->ParseBlock('attributes/attribute/date');
                    break;

                case 'province':
                    $this->defaultCountry = 364;
                    $tpl->SetBlock('attributes/attribute/province');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $provinces = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetProvinces(
                        $this->defaultCountry
                    );
                    if (Jaws_Error::IsError($provinces)) {
                        return $provinces;
                    }
                    if (!empty($provinces)) {
                        $this->lastSelectedProvince = $provinces[0]['province'];
                        foreach ($provinces as $province) {
                            $tpl->SetBlock('attributes/attribute/province/option');
                            $tpl->SetVariable('value', $province['province']);
                            $tpl->SetVariable('text', $province['title']);
                            $tpl->ParseBlock('attributes/attribute/province/option');
                        }
                    }

                    $tpl->ParseBlock('attributes/attribute/province');
                    break;

                case 'city':
                    $tpl->SetBlock('attributes/attribute/city');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    if ($this->lastSelectedProvince > 0) {
                        $cities = Jaws_Gadget::getInstance('Settings')->model->load('Zones')->GetCities(
                            [$this->lastSelectedProvince],
                            $this->defaultCountry
                        );
                        if (Jaws_Error::IsError($cities)) {
                            return $cities;
                        }
                        if (count($cities) > 0) {
                            foreach ($cities as $city) {
                                $tpl->SetBlock('attributes/attribute/city/option');
                                $tpl->SetVariable('value', $city['province']);
                                $tpl->SetVariable('text', $city['title']);
                                $tpl->ParseBlock('attributes/attribute/city/option');
                            }
                        }
                    }

                    $tpl->ParseBlock('attributes/attribute/city');
                    break;

                default:
                    $tpl->SetBlock('attributes/attribute/text');
                    $tpl->SetVariable('attribute_name', $attrName);
                    $tpl->SetVariable('class', isset($attrOptions['class'])? $attrOptions['class'] : '');
                    $tpl->SetVariable('placeholder', '');
                    $tpl->SetVariable('required', '');
                    $tpl->SetVariable('readonly', '');
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

                    if (isset($attrOptions['required']) && $attrOptions['required']) {
                        $tpl->SetVariable('required', 'required');
                    }
                    if (isset($attrOptions['readonly']) && $attrOptions['readonly']) {
                        $tpl->SetVariable('readonly', 'readonly');
                    }
                    if (isset($attrOptions['minlength'])) {
                        $tpl->SetVariable('minlength', $attrOptions['minlength']);
                    }
                    if (isset($attrOptions['maxlength'])) {
                        $tpl->SetVariable('maxlength', $attrOptions['maxlength']);
                    }

                    if (isset($attrOptions['placeholder'])) {
                        $tpl->SetVariable('placeholder', $attrOptions['placeholder']);
                    }

                    $tpl->ParseBlock('attributes/attribute/text');
                    break;
            }

            $tpl->ParseBlock('attributes/attribute');
        }

        $tpl->SetVariable('update', Jaws::t('UPDATE'));
        if ($response = $this->gadget->session->pop('Attributes')) {
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
    function UpdateAttributes()
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
                        $inputAttrs[$attrName] = (bool)$inputAttrs[$attrName];
                        break;

                    case 'date':
                        if (!empty($inputAttrs[$attrName])) {
                            $tmpDate = Jaws_Date::getInstance()->ToBaseDate(
                                preg_split('/[\/\- :]/', $attributes[$aName] . ' 0:0:0')
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
                'Attributes'
            );
        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                RESPONSE_ERROR,
                'Attributes'
            );
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('Attributes', array('gadget' => $objHook->gadget->name)),
            'Attributes'
        );
    }

}