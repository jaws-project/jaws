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
        $this->gadget->layout->setVariable('GADGETS', array_column($gadgets, 'title', 'name'));

        $tpl = $this->gadget->template->loadAdmin('ACLs.html');
        $tpl->SetBlock('ACLs');
        $tpl->SetVariable('menubar', $this->MenuBar('ACLs'));

        $tpl->SetVariable('lbl_permissions', _t('USERS_ACL_PERMISSIONS'));
        $tpl->SetVariable('lbl_groups', _t('USERS_GROUPS_GROUPS'));
        $tpl->SetVariable('lbl_users', _t('USERS_USERS'));

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
        $component = jaws()->request->fetch('component', 'post');
        // fetch default ACLs
        $default_acls = array();
        $result = $GLOBALS['app']->ACL->fetchAll($component);
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
        $post = jaws()->request->fetch(array('component', 'acl'), 'post');
        return array(
            'groups' => $GLOBALS['app']->ACL->fetchGroupsByACL($post['component'], $post['acl']),
            'users' => $GLOBALS['app']->ACL->fetchUsersByACL($post['component'], $post['acl'])
        );
    }
}