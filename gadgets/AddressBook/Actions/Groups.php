<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_Groups extends AddressBook_Actions_Default
{
    /**
     * Layout Action. Displays plane list of Address Book Groups
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Groups()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->model->load('Groups');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $groupItems = $model->GetGroups($user);
        if (Jaws_Error::IsError($groupItems) || !isset($groupItems)) {
            return $groupItems->getMessage(); // TODO: Show intelligible message
        }

        $tpl = $this->gadget->loadTemplate('Groups.html');

        $tpl->SetBlock("group");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_NAME'));
        $tpl->SetVariable('lbl_name',        _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));

        $tpl->SetVariable('address_list_link', $this->gadget->urlMap('AddressBook'));
        $tpl->SetVariable('address_list',    _t('ADDRESSBOOK_ADDRESSBOOK_MANAGE'));
        $tpl->SetVariable('groups_link', $this->gadget->urlMap('ManageGroups'));
        $tpl->SetVariable('groups', _t('ADDRESSBOOK_GROUPS_MANAGE'));

        foreach ($groupItems as $groupItem) {
            $tpl->SetBlock("group/item");
            $tpl->SetVariable('link', $this->gadget->urlMap('GroupMembers', array('id' => $groupItem['id'])));
            $tpl->SetVariable('name', $groupItem['name']);
            $tpl->SetVariable('description', $groupItem['description']);
            $tpl->ParseBlock("group/item");
        }

        $tpl->ParseBlock('group');

        return $tpl->Get();
    }

    /**
     * Displays the list of Address Book Group items, this items can filter by $user(User ID) param.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function ManageGroups()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->model->load('Groups');
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $groupItems = $model->GetGroups($user);
        if (Jaws_Error::IsError($groupItems) || !isset($groupItems)) {
            return $groupItems->getMessage(); // TODO: Show intelligible message
        }

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('ManageGroups.html');

        $tpl->SetBlock("groups");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_GROUP_TITLE'));
        $response = $GLOBALS['app']->Session->PopResponse('AddressBook.Groups');
        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $this->AjaxMe('site_script.js');
        $tpl->SetVariable('menubar', $this->MenuBar('Groups'));
        $tpl->SetVariable('address_list_link', $this->gadget->urlMap('AddressBook'));
        $tpl->SetVariable('address_list',    _t('ADDRESSBOOK_ADDRESSBOOK_MANAGE'));
        $tpl->SetVariable('lbl_name',        _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_actions',     _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('confirmDelete',   _t('ADDRESSBOOK_DELETE_CONFIRM'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('icon_ok', STOCK_OK);

        foreach ($groupItems as $groupItem) {
            $tpl->SetBlock("groups/item");
            $tpl->SetVariable('index', $groupItem['id']);
            $tpl->SetVariable('name',  $groupItem['name']);
            $tpl->SetVariable('description', $groupItem['description']);
            $tpl->SetVariable('view_member_url', $this->gadget->urlMap('GroupMembers', array('id' => $groupItem['id'])));
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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetTitle(_t('ADDRESSBOOK_GROUP_ADD_NEW_TITLE'));
        $tpl = $this->gadget->loadTemplate('EditGroup.html');

        $tpl->SetBlock("group");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_GROUP_ADD_NEW_TITLE'));
        $response = $GLOBALS['app']->Session->PopResponse('AddressBook.Groups');
        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->SetVariable('gid', 0);
        $tpl->SetVariable('action', 'InsertGroup');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_desc', _t('GLOBAL_DESCRIPTION'));

        $tpl->SetVariable('menubar', $this->MenuBar(''));

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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('id'));
        if (empty($rqst['id'])) {
            return false;
        }

        $model = $this->gadget->model->load('Groups');
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
        $response = $GLOBALS['app']->Session->PopResponse('AddressBook.Groups');
        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->SetVariable('gid', $info['id']);
        $tpl->SetVariable('action', 'UpdateGroup');
        $tpl->SetVariable('lbl_name',   _t('GLOBAL_NAME'));
        $tpl->SetVariable('name',       $info['name']);
        $tpl->SetVariable('lbl_desc',   _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('desc',       $info['description']);

        $tpl->SetVariable('menubar', $this->MenuBar(''));

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('GroupMembers', array('id' => $info['id']));
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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'description'), 'post');
        $post['[description]'] = $post['description'];
        unset($post['description']);

        if (empty($post['name']) || trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_GROUPS_EMPTY_NAME_WARNING'), 'AddressBook.Groups', RESPONSE_WARNING);
            Jaws_Header::Referrer();
        }
        $post['[user]'] = $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Groups');
        $result = $model->InsertGroup($post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook.Groups', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_NEW_GROUP_SAVED'), 'AddressBook.Groups');
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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'description'), 'post');
        $gid = (int) jaws()->request->fetch('gid', 'post');

        $model = $this->gadget->model->load('Groups');
        $info = $model->GetGroupInfo($gid);
        if (!isset($info)) {
            return Jaws_HTTPError::Get(404);
        }
        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($post['name']) || trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_GROUPS_EMPTY_NAME_WARNING'), 'AddressBook.Groups', RESPONSE_WARNING);
            Jaws_Header::Referrer();
        }

        $post['[description]'] = $post['description'];
        unset($post['description']);

        $result = $model->UpdateGroup($gid, $post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook.Groups', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_EDIT_GROUP_SAVED'), 'AddressBook.AdrGroups');
            $link = $this->gadget->urlMap('GroupMembers', array('id' => $gid));
            Jaws_Header::Location($link);
        }
    }

    /**
     * Delete Group
     *
     * @access  public
     */
     function DeleteGroup()
     {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $gids = jaws()->request->fetch('gid:array');
        $model = $this->gadget->model->load('Groups');

        $result = $model->DeleteGroups($gids, (int) $GLOBALS['app']->Session->GetAttribute('user'));

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook.Groups', RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_DELETE_GROUP_COMPLETE'), 'AddressBook.Groups');
        }
        Jaws_Header::Location($this->gadget->urlMap('ManageGroups'), 'AddressBook.Groups');
     }
}