<?php
/**
 * Users Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_ACLs extends Users_Actions_Admin_Default
{
    /**
     * Show manage categories interface
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ACLs()
    {
        $this->AjaxMe('script.js');
        $gadgets = Jaws_Gadget::getInstance('Components')->model->load('Gadgets')->GetGadgetsList(null, true, true);
        $this->gadget->define('GADGETS', array_column($gadgets, 'title', 'name'));

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('ACLs') : $menubar;
        return $this->gadget->template->xLoadAdmin('ACLs.html')->render($assigns);


        $tpl = $this->gadget->template->loadAdmin('ACLs.html');
        $tpl->SetBlock('ACLs');
        $tpl->SetVariable('menubar', $this->MenuBar('ACLs'));

        $tpl->SetVariable('lbl_permissions', $this::t('ACL_PERMISSIONS'));
        $tpl->SetVariable('lbl_groups', $this::t('GROUPS_GROUPS'));
        $tpl->SetVariable('lbl_users', $this::t('USERS'));

        $tpl->ParseBlock('ACLs');
        return $tpl->Get();
    }

    /**
     * Returns ACL keys of the component
     *
     * @access  public
     * @return  array   Array of default ACLs and the user/group ACLs
     */
    function GetACLs()
    {
        $component = $this->gadget->request->fetch('component', 'post');
        // fetch default ACLs
        $default_acls = array();
        $result = $this->app->acl->fetchAll($component);
        if (!empty($result)) {
            // set ACL keys description
            $info = Jaws_Gadget::getInstance($component);
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $default_acls[] = array(
                        'key_name'   => $key_name,
                        'key_subkey' => $subkey,
                        'key_value'  => $value,
                        'key_desc'   => $info->acl->description($key_name, $subkey),
                    );
                }
            }
        }
        return $default_acls;
    }

    /**
     * Returns ACL keys of the component
     *
     * @access  public
     * @return  array   Array of default ACLs and the user/group ACLs
     */
    function GetACLGroupsUsers()
    {
        $post = $this->gadget->request->fetch(array('component', 'acl'), 'post');
        return array(
            'groups' => $this->app->acl->fetchGroupsByACL($post['component'], $post['acl']),
            'users' => $this->app->acl->fetchUsersByACL($post['component'], $post['acl'])
        );
    }

    /**
     * Returns ACL keys of the component and user/group
     *
     * @access  public
     * @return  array   Array of default ACLs and the user/group ACLs
     */
    function GetACLKeys()
    {
        $this->gadget->CheckPermission('ManageUserACLs');
        $post = $this->gadget->request->fetch(array('id', 'comp', 'action'), 'post');
        // fetch default ACLs
        $default_acls = array();
        $result = $this->app->acl->fetchAll($post['comp']);
        if (!empty($result)) {
            // set ACL keys description
            $info = Jaws_Gadget::getInstance($post['comp']);
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $default_acls[] = array(
                        'key_name'   => $key_name,
                        'key_subkey' => $subkey,
                        'key_value'  => $value,
                        'key_desc'   => $info->acl->description($key_name, $subkey),
                    );
                }
            }
        }

        // fetch user/group ACLs
        $custom_acls = array();
        $result = ($post['action'] === 'UserACL')?
            $this->app->acl->fetchAllByUser((int)$post['id'], $post['comp']):
            $this->app->acl->fetchAllByGroup((int)$post['id'], $post['comp']);
        if (!empty($result)) {
            foreach ($result as $key_name => $acl) {
                foreach ($acl as $subkey => $value) {
                    $custom_acls[] = array(
                        'key_name'   => $key_name,
                        'key_subkey' => $subkey,
                        'key_value'  => $value,
                    );
                }
            }
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'default_acls' => $default_acls,
                'custom_acls' => $custom_acls
            )
        );
    }

    /**
     * Get object(user/group) acls data
     *
     * @access  public
     * @return  array   Groups data
     */
    function GetObjectACLs()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        if ($post['filters']['action'] === 'User') {
            $acls = $this->app->acl->fetchAllByUser((int)$post['filters']['id']);
        } else {
            $acls = $this->app->acl->fetchAllByGroup((int)$post['filters']['id']);
        }
        if (Jaws_Error::IsError($acls)) {
            return $this->gadget->session->response($acls->getMessage(), RESPONSE_ERROR);
        }
        $objACLs = array();
        foreach ((array)$acls as $comp => $acls) {
            // set ACL keys description
            $info = Jaws_Gadget::getInstance($comp);
            foreach ($acls as $keyName => $keyValue) {
                foreach ($keyValue as $subkey => $val) {
                    $objACLs[] = array(
                        'component' => $comp,
                        'component_title' => _t(strtoupper($comp) . '_TITLE'),
                        'key_name' => $keyName,
                        'subkey' => $subkey,
                        'key_title' => $info->acl->description($keyName, $subkey),
                        'key_value' => $val
                    );
                }
            }
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => count($objACLs),
                'records' => $objACLs
            )
        );
    }

}