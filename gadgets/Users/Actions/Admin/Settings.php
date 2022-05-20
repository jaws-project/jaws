<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Settings extends Users_Actions_Admin_Default
{
    /**
     * Builds admin settings UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('ManageSettings');
        $this->AjaxMe('script.js');

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Settings') : $menubar;
        $assigns['authtypes'] = $this->gadget->model->loadAdmin('Settings')->GetAuthTypes();
        $assigns['authtype_default'] = $this->gadget->registry->fetch('authtype');
        $assigns['anon_register'] = $this->gadget->registry->fetch('anon_register');
        $assigns['anon_activation_items'] = array(
            'auto' => $this::t('PROPERTIES_ACTIVATION_AUTO'),
            'user' => $this::t('PROPERTIES_ACTIVATION_BY_USER'),
            'admin' => $this::t('PROPERTIES_ACTIVATION_BY_ADMIN'),
        );
        $assigns['anon_activation_default'] = $this->gadget->registry->fetch('anon_activation');
        $assigns['anon_group'] = $this->gadget->registry->fetch('anon_group');
        $assigns['groups'] = $this->gadget->model->load('Group')->list(
            0, 0, 0,
            array('enabled'  => true),
            array(), // default fieldset
            array('title' => true ) // order by title ascending
        )
        $assigns['password_recovery'] = $this->gadget->registry->fetch('password_recovery');
        $assigns['reserved_users'] = trim($this->gadget->registry->fetch('reserved_users'));
        return $this->gadget->template->xLoadAdmin('Settings.html')->render($assigns);
    }

    /**
     * Updates settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateSettings()
    {
        $this->gadget->CheckPermission('ManageSettings');
        $settings = Jaws::getInstance()->request->fetchAll('post');
        $settings['reserved_users'] = implode(
            "\n",
            array_filter(preg_split("/\n|\r|\n\r/", strtolower($settings['reserved_users'])))
        );

        if ($this->gadget->model->loadAdmin('Settings')->UpdateSettings($settings)) {
            return $this->gadget->session->response(
                $this::t('PROPERTIES_UPDATED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->response(
            $this::t('PROPERTIES_CANT_UPDATE'),
            RESPONSE_ERROR
        );
    }

}