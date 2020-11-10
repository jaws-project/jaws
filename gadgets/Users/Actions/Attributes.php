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

        $this->gadget->CheckPermission('EditUsersAttributes');
        $this->AjaxMe('index.js');

        // find real gadget name
        $gPath = ROOT_JAWS_PATH. 'gadgets'. DIRECTORY_SEPARATOR;
        $hooks = glob($gPath . '*/Hooks/UsersAttributes.php');
        $gadgets = preg_replace(
            '@'.preg_quote($gPath, '@'). '(\w*)/Hooks/UsersAttributes.php@',
            '${1}',
            $hooks
        );
        if (false === $indx = array_search(strtolower($gadget), array_map('strtolower', $gadgets))) {
            return Jaws_HTTPError::Get(404);
        }
        $gadget = $gadgets[$indx];

        $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('UsersAttributes');
        if (Jaws_Error::IsError($objHook)) {
            return Jaws_HTTPError::Get(404);
        }

        $attrs = $objHook->Execute();
        if (Jaws_Error::IsError($options) || empty($attrs)) {
            return Jaws_HTTPError::Get(500);
        }

        // Load the template
        $tpl = $this->gadget->template->load('Attributes.html');
        $tpl->SetBlock('attributes');
        $tpl->SetVariable('title', Jaws_Gadget::t("$gadget.TITLE"));
        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        /*
        --
        --
        --
        --
        --
        --

        */

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

        // check permission
        $this->gadget->CheckPermission('EditUserAttributes');
        $postedData = $this->gadget->request->fetchAll('post');

        /*
        --
        --
        --
        --
        --
        --

        */

        if (!Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $this::t('ATTRIBUTES_UPDATED'),
                RESPONSE_NOTICE,
                'Attributes'
            );
        } else {
            $this->gadget->session->push(
                $result->GetMessage(),
                RESPONSE_ERROR,
                'Attributes'
            );
        }

        return Jaws_Header::Location(
            $this->gadget->urlMap('Attributes', array('gadget' => $postedData['gadget'])),
            'Attributes'
        );
    }

}