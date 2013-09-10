<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class AddressBook_Actions_Groups extends Jaws_Gadget_HTML
{
    /**
     * Displays the list of Address Book Group items, this items can filter by $user(User ID) param.
     *
     * @access  public
     * $param   int/string  $user   User ID, Show Groups for this user
     * @return  string HTML content with menu and menu items
     */
    function ManageGroups()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->load('Model')->load('Model', 'Groups');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $groupItems = $model->GetGroups($user);
        if (Jaws_Error::IsError($groupItems) || !isset($groupItems)) {
            return $groupItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('ManageGroups.html');

        $tpl->SetBlock("groups");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('groups/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('groups/response');
        }
        $link = $this->gadget->urlMap('AddressList');
        $tpl->SetVariable('address_list_link', $link);
        $tpl->SetVariable('address_list', _t('ADDRESSBOOK_ADDRESSBOOK_MANAGE'));

        $tpl->SetVariable('lbl_name', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));

        foreach ($groupItems as $groupItem) {
            $tpl->SetBlock("groups/item");
            $tpl->SetVariable('name', $groupItem['name']);
            $tpl->SetVariable('description', $groupItem['description']);

            //Edite Item, TODO: Check user can do this action
            $tpl->SetBlock('groups/item/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_EDIT'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('EditGroup', array('id' => $groupItem['id'])));
            $tpl->ParseBlock('groups/item/action');

            //Delete Item, TODO: Check user can do this action
            $tpl->SetBlock('groups/item/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_DELETE'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('DeleteGroup', array('id' => $groupItem['id'])));
            $tpl->ParseBlock('groups/item/action');

            $tpl->ParseBlock("groups/item");
        }

        $tpl->SetBlock("groups/actions");
        $tpl->SetVariable('add_group', _t('ADDRESSBOOK_GROUPS_ADD'));
        $link = $this->gadget->urlMap('AddGroup');
        $tpl->SetVariable('add_group_link', $link);
        $tpl->ParseBlock("groups/actions");

        $tpl->ParseBlock('groups');

        return $tpl->Get();
    }

    /**
     * Displays form for add new AddressBook Group item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function AddGroup()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetTitle(_t('ADDRESSBOOK_GROUP_ADD_NEW_TITLE'));
        $tpl = $this->gadget->loadTemplate('EditGroup.html');

        $tpl->SetBlock("group");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_GROUP_ADD_NEW_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('group/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('group/response');
        }

        $tpl->SetVariable('gid', 0);
        $tpl->SetVariable('action', 'InsertGroup');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_desc', _t('GLOBAL_DESCRIPTION'));

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('ManageGroups');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('group');

        return $tpl->Get();
    }

    /**
     * Displays form for edit AddressBook Group item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function EditGroup()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('id'));
        if (empty($rqst['id'])) {
            return false;
        }

        $model = $this->gadget->load('Model')->load('Model', 'Groups');
        $info = $model->GetGroupInfo((int) $rqst['id']);
        if (Jaws_Error::IsError($info)) {
            return $info->getMessage(); // TODO: Show intelligible message
        }

        if (!isset($info)) {
            return Jaws_HTTPError::Get(404);
        }

        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetTitle(_t('ADDRESSBOOK_GROUP_EDIT_TITLE'));
        $tpl = $this->gadget->loadTemplate('EditGroup.html');

        $tpl->SetBlock("group");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_GROUP_EDIT_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('group/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('group/response');
        }

        $tpl->SetVariable('gid', $info['id']);
        $tpl->SetVariable('action', 'UpdateGroup');
        $tpl->SetVariable('lbl_name',   _t('GLOBAL_NAME'));
        $tpl->SetVariable('name',       $info['name']);
        $tpl->SetVariable('lbl_desc',   _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('desc',       $info['description']);

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('ManageGroups');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('group');

        return $tpl->Get();
    }

    /**
     * Insert New AddressBook Group Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function InsertGroup()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'description'), 'post');
        $post['[description]'] = $post['description'];
        unset($post['description']);

        $post['[user]'] = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->load('Model')->load('Model', 'Groups');
        $result = $model->InsertGroup($post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
            Jaws_Header::Referrer();
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_NEW_GROUP_SAVED'), 'AddressBook');
            $link = $this->gadget->urlMap('ManageGroups');
            Jaws_Header::Location($link);
        }
    }

    /**
     * Update Selected AddressBook Group Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function UpdateGroup()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'description'), 'post');
        $gid = (int) jaws()->request->fetch('gid', 'post');

        $model = $this->gadget->load('Model')->load('Model', 'Groups');
        $info = $model->GetGroupInfo($gid);
        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
            return Jaws_HTTPError::Get(403);
        }

        $post['[description]'] = $post['description'];
        unset($post['description']);

        $result = $model->UpdateGroup($gid, $post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
            Jaws_Header::Referrer();
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_EDIT_GROUP_SAVED'), 'AddressBook');
            $link = $this->gadget->urlMap('ManageGroups');
            Jaws_Header::Location($link);
        }
    }
}
















