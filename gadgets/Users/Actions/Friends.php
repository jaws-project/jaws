<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Friends extends Users_Actions_Default
{
    /**
     * Prepares a form for manage user's friends groups
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function FriendsGroups()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('ManageFriends');
        $this->AjaxMe('index.js');
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_name', _t('GLOBAL_NAME'));
        $this->gadget->define('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));
        $this->gadget->define('lbl_addFriend', _t('USERS_FRIENDS_ADD'));
        $this->gadget->define('lbl_editFriend', _t('USERS_FRIENDS_EDIT'));
        $this->gadget->define('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->define('lbl_manageFriends', _t('USERS_FRIENDS_MANAGE'));

        $response = $GLOBALS['app']->Session->PopResponse('Users.Groups');
        // Load the template
        $tpl = $this->gadget->template->load('Friends.html');
        $tpl->SetBlock('groups');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        $tpl->SetVariable('title', _t('USERS_FRIENDS'));

        // Users
        $uModel = new Jaws_User();
        $superadmin = $GLOBALS['app']->Session->IsSuperAdmin() ? null : false;
        $users = $uModel->GetUsers(false, $superadmin);
        if (!Jaws_Error::IsError($users)) {
            foreach ($users as $user) {
                $tpl->SetBlock('groups/user');
                $tpl->SetVariable('id', $user['id']);
                $tpl->SetVariable('title', $user['nickname']. ' ('. $user['username']. ')');
                $tpl->ParseBlock('groups/user');
            }
        }

        $tpl->SetVariable('lbl_addFriend', _t('USERS_FRIENDS_ADD'));
        $tpl->SetVariable('lbl_manageFriends', _t('USERS_FRIENDS_MANAGE'));
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));

        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_add_group', _t('USERS_ADD_GROUP'));
        $tpl->SetVariable('url_add_group', $this->gadget->urlMap('FriendsGroupUI'));

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));

        $tpl->ParseBlock('groups');
        return $tpl->Get();
    }

    /**
     * Get friends groups list
     *
     * @access  public
     * @return  JSON
     */
    function GetFriendGroups()
    {
        $this->gadget->CheckPermission('ManageFriends');
        $post = jaws()->request->fetch(
            array('filters:array', 'limit', 'offset', 'searchLogic', 'search:array', 'sort:array'),
            'post'
        );

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;
        $groups = $jUser->GetGroups($user, $post['limit'], $post['offset']);

        foreach($groups as $key=>$group) {
            $group['recid'] = $group['id'];
            $groups[$key] = $group;
        }
        $groupsCount = $jUser->GetUserContactsCount($user);

        return array(
            'status' => 'success',
            'total' => $groupsCount,
            'records' => $groups
        );
    }

    /**
     * Get a friend group info
     *
     * @access  public
     * @return  JSON
     */
    function GetFriendGroup()
    {
        $this->gadget->CheckPermission('ManageFriends');
        $id = jaws()->request->fetch('id', 'post');

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;
        return $jUser->GetGroup($id, $user);
    }

    /**
     * Add or Update a friend group
     *
     * @access  public
     * @return  void
     */
    function SaveFriendGroup()
    {
        $this->gadget->CheckPermission('ManageFriends');

        $post = jaws()->request->fetch(array('id', 'data:array'), 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $jUser = new Jaws_User;

        // Update group
        if(!empty($post['id'])) {
            $res = $jUser->UpdateGroup($post['id'], $post['data'], $user);
            // Add new group
        } else {
            unset($post['id']);
            $res = $jUser->AddGroup($post['data'], $user);
        }

        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUPS_CREATED', $post['data']['title']), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete user's friend group(s)
     *
     * @access  public
     * @return  void
     */
    function DeleteFriendGroups()
    {
        $this->gadget->CheckPermission('ManageFriends');

        $ids = jaws()->request->fetch('ids:array', 'post');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        if (!empty($ids)) {
            $jUser = new Jaws_User;
            foreach($ids as $id) {
                // TODO: improve performance
                $res= $jUser->DeleteGroup($id, $user);
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushResponse(
                        $res->getMessage(),
                        'Users.Groups',
                        RESPONSE_ERROR
                    );
                    break;
                }
            }

            if (Jaws_Error::isError($res)) {
                return $GLOBALS['app']->Session->GetResponse($res->GetMessage(), RESPONSE_ERROR);
            } else {
                return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUP_DELETED'), RESPONSE_NOTICE);
            }
        }
    }

    /**
     * Adds a group of users(by their IDs) to a certain group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddUsersToFriendGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $post = jaws()->request->fetch(array('gid', 'users:array'), 'post');

        $uModel = $this->gadget->model->load('Friends');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = $uModel->AddUsersToGroup((int)$post['gid'], $post['users'], $user);

        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUP_CANNOT_ADD_USER'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('USERS_GROUP_ADDED_USER'), RESPONSE_NOTICE);
        }
    }
}