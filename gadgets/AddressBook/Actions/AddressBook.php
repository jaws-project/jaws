<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_AddressBook extends AddressBook_Actions_Default
{
    /**
     * Displays the list of Address Book items, this items can filter by $uid(user ID) param.
     *
     * @access  public
     * $gid     Group ID
     * @return  string HTML content with menu and menu items
     */
     function AddressBook()
     {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $uid = (int) $GLOBALS['app']->Session->GetAttribute('user');

        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($uid);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        $this->AjaxMe('site_script.js');
        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->template->load('AddressBook.html');
        $tpl->SetBlock("address_list");

        $tpl->SetVariable('title', _t('ADDRESSBOOK_NAME'));
        $tpl->SetVariable('confirmDelete', _t('ADDRESSBOOK_DELETE_CONFIRM'));
        // Set default delete URL for use in javascript
        $tpl->SetVariable('deleteURL', $this->gadget->urlMap('DeleteAddress', array('id' => '')));

        $response = $GLOBALS['app']->Session->PopResponse('AddressBook');
        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->SetVariable('menubar', $this->MenuBar('AddressBook'));
        $tpl->SetVariable('lbl_group',  _t('ADDRESSBOOK_GROUP'));
        $tpl->SetVariable('lbl_term',   _t('ADDRESSBOOK_TERM'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_export', _t('ADDRESSBOOK_EXPORT_VCARD'));
        $tpl->SetVariable('lbl_all_groups', _t('GLOBAL_ALL_GROUPS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));
        $tpl->SetVariable('lbl_search', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('icon_ok', STOCK_OK);
        $gModel = $this->gadget->model->load('Groups');
        $groupList = $gModel->GetGroups($uid);
        foreach ($groupList as $gInfo) {
            $tpl->SetBlock('address_list/group_item');
            $tpl->SetVariable('group_name', $gInfo['name']);
            $tpl->SetVariable('gid', $gInfo['id']);
            $tpl->ParseBlock('address_list/group_item');
        }
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);

        $tpl->SetVariable('addressbook', $this->AddressList());

        // Add New
        $tpl->SetBlock("address_list/actions");
        $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_ITEMS_ADD'));
        $tpl->SetVariable('action_url', $this->gadget->urlMap('AddAddress'));
        $tpl->ParseBlock("address_list/actions");

        // Import vCard
        $tpl->SetBlock("address_list/actions");
        $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_IMPORT_VCARD'));
        $tpl->SetVariable('action_url', $this->gadget->urlMap('VCardImport'));
        $tpl->ParseBlock("address_list/actions");

        $tpl->ParseBlock('address_list');

        return $tpl->Get();
     }

    /**
     * Displays the list of Address Book items, this items can filter by $uid(user ID) param.
     *
     * @access  public
     * @param   int     $uid     User ID
     * @param   int     $gid     Group ID
     * @param   string  $term    Search term
     * @return  string HTML content with menu and menu items
     */
    function AddressList($gid = null, $term = '')
    {
        $uid = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('AddressBook');

        if (empty($gid) && $term == '') {
            $rqst = jaws()->request->fetch(array('gid:int', 'term'));
            $gid = $rqst['gid'];
            $term = $rqst['term'];
        }

        $addressItems = $model->GetAddressList($uid, $gid, false, $term);
        if (Jaws_Error::IsError($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }

        $tpl = $this->gadget->template->load('AddressList.html');
        $tpl->SetBlock("list");

        $tpl->SetVariable('lbl_name',      _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_title',     _t('ADDRESSBOOK_ITEMS_TITLE'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("list/item1");
            $tpl->SetVariable('index', $addressItem['id']);
            $tpl->SetVariable('name', str_replace(';' , ' ', $addressItem['name']));
            $tpl->SetVariable('view_url', $this->gadget->urlMap('View', array('id' => $addressItem['id'])));
            $tpl->SetVariable('title', $addressItem['title']);
            if ($addressItem['public']) {
                $tpl->SetBlock('list/item1/is_public');
                $tpl->SetVariable('icon_public', STOCK_ABOUT);
                $tpl->ParseBlock('list/item1/is_public');
            }

            $tpl->ParseBlock("list/item1");
        }
        $tpl->ParseBlock("list");
        return $tpl->Get();
    }

    /**
     * Displays form for add new AddressBook item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function AddAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_ADD_NEW_TITLE'));
        $tpl = $this->gadget->template->load('EditAddress.html');

        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_ADD_NEW_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', 0);
        $tpl->SetVariable('lastID', 1);
        $tpl->SetVariable('action', 'InsertAddress');
        $tpl->SetVariable('lbl_user_link',  _t('ADDRESSBOOK_ITEMS_USER_LINK'));
        $tpl->SetVariable('lbl_firstname',    _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lastname',     _t('ADDRESSBOOK_ITEMS_LASTNAME'));
        $tpl->SetVariable('lbl_title',        _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_nickname',     _t('ADDRESSBOOK_ITEMS_NICKNAME'));
        $tpl->SetVariable('lbl_notes',        _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_public',       _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('lbl_upload_image', _t('ADDRESSBOOK_PERSON_IMAGE_UPLOAD'));
        $tpl->SetVariable('lbl_delete_image', _t('ADDRESSBOOK_PERSON_IMAGE_DELETE'));
        $tpl->SetVariable('tel_title',        _t('ADDRESSBOOK_TEL_TITLE'));
        $tpl->SetVariable('email_title',      _t('ADDRESSBOOK_EMAIL_TITLE'));
        $tpl->SetVariable('adr_title',        _t('ADDRESSBOOK_ADR_TITLE'));
        $tpl->SetVariable('group_title',      _t('ADDRESSBOOK_GROUP_TITLE'));
        $tpl->SetVariable('other_details',    _t('ADDRESSBOOK_OTHER_DETAILS'));
        $tpl->SetVariable('nameEmptyWarning', _t('ADDRESSBOOK_EMPTY_NAME_WARNING'));
        $tpl->SetVariable('public_title',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('url_title',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('toggle_max',     STOCK_ADD);
        $tpl->SetVariable('toggle_min',     STOCK_REMOVE);
        $tpl->SetVariable('upload_icon',    STOCK_ADD);
        $tpl->SetVariable('delete_icon',    STOCK_DELETE);

        $tpl->SetVariable('baseSiteUrl', $GLOBALS['app']->getSiteURL());
        $tpl->SetVariable('loadImageUrl', $this->gadget->urlMap('LoadImage', array('file' => '')));
        $current_image = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/Resources/images/photo128px.png');
        $tpl->SetVariable('image_src', $current_image);

        $tpl->SetVariable('menubar', $this->MenuBar(''));

        $uModel = new Jaws_User();
        $users = $uModel->GetUsers();
        $tpl->SetBlock('address/user_item');
        $tpl->SetVariable('user_id', 0);
        $tpl->SetVariable('user_name', '');
        $tpl->ParseBlock('address/user_item');
        foreach ($users as $user) {
            $tpl->SetBlock('address/user_item');
            $tpl->SetVariable('user_id', $user['id']);
            $tpl->SetVariable('user_name', $user['nickname']);
            $tpl->ParseBlock('address/user_item');
        }
        $tpl->SetVariable('icon_load', STOCK_REFRESH);

        $uid = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('AddressBook');
        $addressItems = $model->GetAddressList($uid, 0);
        $tpl->SetVariable('lbl_related', _t('ADDRESSBOOK_LABEL_RALATED'));
        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock('address/relation_item');
            $tpl->SetVariable('address_id', $addressItem['id']);
            $tpl->SetVariable('fn', str_replace(';', ' ', $addressItem['name']));
            $tpl->ParseBlock('address/relation_item');
        }

        $iIndex = 1;
        $this->GetItemsCombo($tpl, 'tel', ':', $iIndex, $this->_TelTypes);
        $this->GetItemsCombo($tpl, 'email', ':', $iIndex, $this->_EmailTypes);
        $this->GetItemsCombo($tpl, 'adr', ':', $iIndex, $this->_AdrTypes);
        $this->GetItemsInput($tpl, 'url', array(''), $iIndex);

        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $gModel = $this->gadget->model->load('Groups');
        $groupList = $gModel->GetGroups($user);

        foreach ($groupList as $gInfo) {
            $tpl->SetBlock('address/group');
            $tpl->SetVariable('lbl_group', $gInfo['name']);
            $tpl->SetVariable('gid', $gInfo['id']);
            $tpl->ParseBlock('address/group');
        }

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressBook');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }

    /**
     * Displays form for edit AddressBook item.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function EditAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int) jaws()->request->fetch('id');
        if ($id == 0) {
            return false;
        }

        $model = $this->gadget->model->load('AddressBook');
        $info = $model->GetAddressInfo($id);
        if (Jaws_Error::IsError($info)) {
            return $info->getMessage(); // TODO: Show intelligible message
        }

        if (!isset($info)) {
            return Jaws_HTTPError::Get(404);
        }

        // Check this ID for Me, And Can I Edit Or View This?!
        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
            return Jaws_HTTPError::Get(403);
        }

        $this->AjaxMe('site_script.js');
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        $tpl = $this->gadget->template->load('EditAddress.html');
        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('action', 'UpdateAddress');
        $tpl->SetVariable('lbl_user_link',    _t('ADDRESSBOOK_ITEMS_USER_LINK'));
        $tpl->SetVariable('lbl_firstname',    _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_lastname',     _t('ADDRESSBOOK_ITEMS_LASTNAME'));
        $tpl->SetVariable('lbl_title',        _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_nickname',     _t('ADDRESSBOOK_ITEMS_NICKNAME'));
        $tpl->SetVariable('lbl_url',          _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes',        _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_public',       _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('tel_title',        _t('ADDRESSBOOK_TEL_TITLE'));
        $tpl->SetVariable('email_title',      _t('ADDRESSBOOK_EMAIL_TITLE'));
        $tpl->SetVariable('adr_title',        _t('ADDRESSBOOK_ADR_TITLE'));
        $tpl->SetVariable('group_title',      _t('ADDRESSBOOK_GROUP_TITLE'));
        $tpl->SetVariable('other_details',    _t('ADDRESSBOOK_OTHER_DETAILS'));
        $tpl->SetVariable('nameEmptyWarning', _t('ADDRESSBOOK_EMPTY_NAME_WARNING'));
        $tpl->SetVariable('lbl_save',         _t('GLOBAL_SAVE'));
        $tpl->SetVariable('public_title',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('url_title',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('toggle_max',     STOCK_ADD);
        $tpl->SetVariable('toggle_min',     STOCK_REMOVE);
        $tpl->SetVariable('title',          $info['title']);
        $tpl->SetVariable('nickname',       $info['nickname']);
        $tpl->SetVariable('url',            $info['url']);
        $tpl->SetVariable('notes',          $info['notes']);
        $tpl->SetVariable('upload_icon',    STOCK_ADD);
        $tpl->SetVariable('delete_icon',    STOCK_DELETE);

        $tpl->SetVariable('baseSiteUrl', $GLOBALS['app']->getSiteURL());
        $tpl->SetVariable('loadImageUrl', $this->gadget->urlMap('LoadImage', array('file' => '')));

        if ($info['public']) {
            $tpl->SetBlock('address/selected');
            $tpl->ParseBlock('address/selected');
        }

        $tpl->SetVariable('menubar', $this->MenuBar(''));

        $uModel = new Jaws_User();
        $users = $uModel->GetUsers();
        $tpl->SetBlock('address/user_item');
        $tpl->SetVariable('user_id', 0);
        $tpl->SetVariable('user_name', '');
        $tpl->ParseBlock('address/user_item');
        foreach ($users as $user) {
            $tpl->SetBlock('address/user_item');
            $tpl->SetVariable('user_id', $user['id']);
            $tpl->SetVariable('user_name', $user['nickname']);
            $tpl->SetVariable('selected', ($user['id'] == $info['user_link'])? 'selected="selected"': '');
            $tpl->ParseBlock('address/user_item');
        }
        $tpl->SetVariable('icon_load', STOCK_REFRESH);

        $addressItems = $model->GetAddressList($info['user'], 0);
        $tpl->SetVariable('lbl_related', _t('ADDRESSBOOK_LABEL_RALATED'));
        foreach ($addressItems as $addressItem) {
            if ($addressItem['id'] != $info['id']) {
                $tpl->SetBlock('address/relation_item');
                $tpl->SetVariable('address_id', $addressItem['id']);
                $tpl->SetVariable('fn', str_replace(';', ' ', $addressItem['name']));
                $tpl->SetVariable('selected', ($addressItem['id'] == $info['related'])? 'selected="selected"': '');
                $tpl->ParseBlock('address/relation_item');
            }
        }

        $names = explode(';', $info['name']);
        foreach ($names as $key => $name) {
            $tpl->SetVariable('name' . $key, $name);
        }

        if (empty($info['image'])) {
            $current_image = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/Resources/images/photo128px.png');
        } else {
            $current_image = $GLOBALS['app']->getDataURL() . "addressbook/image/" . $info['image'];
            $current_image .= !empty($info['updatetime']) ? "?" . $info['updatetime'] . "" : '';
        }
        $tpl->SetVariable('image_src', $current_image);

        // upload/delete image
        $tpl->SetVariable('lbl_upload_image', _t('ADDRESSBOOK_PERSON_IMAGE_UPLOAD'));
        $tpl->SetVariable('lbl_delete_image', _t('ADDRESSBOOK_PERSON_IMAGE_DELETE'));

        // Tel
        $iIndex = 1;
        $this->GetItemsCombo($tpl, 'tel', $info['tel_home'], $iIndex, $this->_TelTypes);
        $this->GetItemsCombo($tpl, 'tel', $info['tel_work'], $iIndex, $this->_TelTypes);
        $this->GetItemsCombo($tpl, 'tel', $info['tel_other'], $iIndex, $this->_TelTypes);
        if ($iIndex == 1) {
            $this->GetItemsCombo($tpl, 'tel', ':', $iIndex, $this->_TelTypes);
        }

        // Email
        $iIndex = 1;
        $this->GetItemsCombo($tpl, 'email', $info['email_home'], $iIndex, $this->_EmailTypes);
        $this->GetItemsCombo($tpl, 'email', $info['email_work'], $iIndex, $this->_EmailTypes);
        $this->GetItemsCombo($tpl, 'email', $info['email_other'], $iIndex, $this->_EmailTypes);
        if ($iIndex == 1) {
            $this->GetItemsCombo($tpl, 'email', ':', $iIndex, $this->_EmailTypes);
        }

        // Address
        $iIndex = 1;
        $this->GetItemsCombo($tpl, 'adr', $info['adr_home'], $iIndex, $this->_AdrTypes, '\n');
        $this->GetItemsCombo($tpl, 'adr', $info['adr_work'], $iIndex, $this->_AdrTypes, '\n');
        $this->GetItemsCombo($tpl, 'adr', $info['adr_other'], $iIndex, $this->_AdrTypes, '\n');
        if ($iIndex == 1) {
            $this->GetItemsCombo($tpl, 'adr', ':', $iIndex, $this->_AdrTypes);
        }

        // URL
        $iIndex = 1;
        if (trim($info['url']) != '') {
            $urls = explode('\n', $info['url']);
            $this->GetItemsInput($tpl, 'url', $urls, $iIndex);
        }
        if ($iIndex == 1) {
            $this->GetItemsInput($tpl, 'url', array(''), $iIndex);
        }
        $tpl->SetVariable('lastID', $iIndex);

        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $gModel = $this->gadget->model->load('Groups');
        $groupList = $gModel->GetGroups($user);

        $agModel = $this->gadget->model->load('AddressBookGroup');
        $agData = $agModel->GetGroupIDs($info['id'], $user);
        $groupAux = array();
        foreach ($agData as $group) {
            $groupAux[] = $group;
        }

        foreach ($groupList as $gInfo) {
            $tpl->SetBlock('address/group');
            if (in_array($gInfo['id'], $groupAux)) {
                $tpl->SetBlock('address/group/selected_group');
                $tpl->ParseBlock('address/group/selected_group');
            }
            $tpl->SetVariable('lbl_group', $gInfo['name']);
            $tpl->SetVariable('gid', $gInfo['id']);
            $tpl->ParseBlock('address/group');
        }

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressBook');
        $tpl->SetVariable('cancel_url', $link);

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }

    /**
     * Insert New Address Book Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function InsertAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('nickname', 'title', 'image', 'related',
                                             'notes', 'public', 'user_link:int'), 'post');
        $post['name'] = implode(';', jaws()->request->fetch('name:array', 'post'));

        $groupIDs = jaws()->request->fetch('groups:array');
        $tels = jaws()->request->fetch(array('tel_type:array', 'tel_number:array'), 'post');

        $post['[user]'] = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('AddressBook');

        $telHome = array();
        $telWork = array();
        $telOther = array();
        if (isset($tels['tel_type'])) {
            foreach ($tels['tel_number'] as $key => $telNumber) {
                if (trim($telNumber) != '') {
                    switch ($tels['tel_type'][$key]) {
                        case 1: //Home
                        case 2:
                        case 3:
                            $telHome[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                        case 4: //Work
                        case 5:
                        case 6:
                            $telWork[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                        case 7: //Other
                        case 8:
                        case 9:
                            $telOther[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                    }
                }
            }
        }
        $post['tel_home'] = implode(',', $telHome);
        $post['tel_work'] = implode(',', $telWork);
        $post['tel_other'] = implode(',', $telOther);

        $emails = jaws()->request->fetch(array('email_type:array', 'email:array'), 'post');
        $emailHome = array();
        $emailWork = array();
        $emailOther = array();
        if (isset($emails['email_type'])) {
            foreach ($emails['email'] as $key => $email) {
                if (trim($email) != '') {
                    switch ($emails['email_type'][$key]) {
                        case 1: //Home
                            $emailHome[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                        case 2: //Work
                            $emailWork[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                        case 3: //Other
                            $emailOther[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                    }
                }
            }
        }
        $post['email_home'] = implode(',', $emailHome);
        $post['email_work'] = implode(',', $emailWork);
        $post['email_other'] = implode(',', $emailOther);

        $adrs = jaws()->request->fetch(array('adr_type:array', 'adr:array'), 'post');
        $adrHome = array();
        $adrWork = array();
        $adrOther = array();
        $arrSearch = array("\r\n", "\n", "\r");
        if (isset($adrs['adr_type'])) {
            foreach ($adrs['adr'] as $key => $adr) {
                $adr = str_replace($arrSearch, ' ', $adr);
                if (trim($adr) != '') {
                    switch ($adrs['adr_type'][$key]) {
                        case 1: //Home
                            $adrHome[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                        case 2: //Work
                            $adrWork[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                        case 3: //Other
                            $adrOther[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                    }
                }
            }
        }
        $post['adr_home'] = implode('\n', $adrHome);
        $post['adr_work'] = implode('\n', $adrWork);
        $post['adr_other'] = implode('\n', $adrOther);

        $urls = jaws()->request->fetch('url:array', 'post');
        $post['url'] = implode('\n', $urls);

        $adrID = $model->InsertAddress($post);

        if (Jaws_Error::IsError($adrID)) {
            $GLOBALS['app']->Session->PushResponse($adrID->getMessage(), 'AddressBook', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        } else {
            if (is_array($groupIDs) && count($groupIDs) > 0) {
                $agModel = $this->gadget->model->load('AddressBookGroup');
                foreach ($groupIDs as $gid => $group) {
                    $agModel->AddGroupToAddress($adrID, $gid, $post['[user]']);
                }
            }

            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_NEW_ADDRESS_SAVED'), 'AddressBook');
            Jaws_Header::Location($this->gadget->urlMap('AddressBook'));
        }
    }

    /**
     * Update Address Book Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function UpdateAddress()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('nickname', 'title', 'user_link:int', 'related',
                                             'image', 'url', 'notes', 'public', 'id'),
                                    'post');

        $post['name'] = implode(';', jaws()->request->fetch('name:array', 'post'));

        $id = (int) $post['id'];
        unset($post['id']);
        $groupIDs = jaws()->request->fetch('groups:array');

        $tels = jaws()->request->fetch(array('tel_type:array', 'tel_number:array'), 'post');

        $model = $this->gadget->model->load('AddressBook');

        // Check user edit His addressBook
        $addressInfo = $model->GetAddressInfo($id);
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        if (Jaws_Error::IsError($addressInfo) || !isset($addressInfo) || ($user != $addressInfo['user'])) {
            return Jaws_HTTPError::Get(403);
        }

        $telHome = array();
        $telWork = array();
        $telOther = array();
        if (isset($tels['tel_type'])) {
            foreach ($tels['tel_number'] as $key => $telNumber) {
                if (trim($telNumber) == '') {
                    unset($tels['tel_number'][$key]);
                    unset($tels['tel_type'][$key]);
                } else {
                    switch ($tels['tel_type'][$key]) {
                        case 1: //Home
                        case 2:
                        case 3:
                            $telHome[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                        case 4: //Work
                        case 5:
                        case 6:
                            $telWork[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                        case 7: //Other
                        case 8:
                        case 9:
                            $telOther[] = $tels['tel_type'][$key] . ':' . $telNumber;
                            break;
                    }
                }
            }
        }
        $post['tel_home'] = implode(',', $telHome);
        $post['tel_work'] = implode(',', $telWork);
        $post['tel_other'] = implode(',', $telOther);

        $emails = jaws()->request->fetch(array('email_type:array', 'email:array'), 'post');
        $emailHome = array();
        $emailWork = array();
        $emailOther = array();
        if (isset($emails['email_type'])) {
            foreach ($emails['email'] as $key => $email) {
                if (trim($email) != '') {
                    switch ($emails['email_type'][$key]) {
                        case 1: //Home
                            $emailHome[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                        case 2: //Work
                            $emailWork[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                        case 3: //Other
                            $emailOther[] = $emails['email_type'][$key] . ':' . $email;
                            break;
                    }
                }
            }
        }
        $post['email_home'] = implode(',', $emailHome);
        $post['email_work'] = implode(',', $emailWork);
        $post['email_other'] = implode(',', $emailOther);

        $adrs = jaws()->request->fetch(array('adr_type:array', 'adr:array'), 'post');
        $adrHome = array();
        $adrWork = array();
        $adrOther = array();
        $arrSearch = array("\r\n", "\n", "\r");
        if (isset($adrs['adr_type'])) {
            foreach ($adrs['adr'] as $key => $adr) {
                $adr = str_replace($arrSearch, ' ', $adr);
                if (trim($adr) != '') {
                    switch ($adrs['adr_type'][$key]) {
                        case 1: //Home
                            $adrHome[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                        case 2: //Work
                            $adrWork[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                        case 3: //Other
                            $adrOther[] = $adrs['adr_type'][$key] . ':' . $adr;
                            break;
                    }
                }
            }
        }
        $post['adr_home'] = implode('\n', $adrHome);
        $post['adr_work'] = implode('\n', $adrWork);
        $post['adr_other'] = implode('\n', $adrOther);

        $urls = jaws()->request->fetch('url:array', 'post');
        $post['url'] = implode('\n', $urls);

        $result = $model->UpdateAddress($id, $post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook', RESPONSE_ERROR);
            Jaws_Header::Referrer();
        } else {
            $agModel = $this->gadget->model->load('AddressBookGroup');
            $agModel->DeleteGroupForAddress($id, $addressInfo['user']);
            if (is_array($groupIDs) && count($groupIDs) > 0) {
                foreach ($groupIDs as $gid) {
                    $agModel->AddGroupToAddress($id, $gid, $user);
                }
            }

            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_EDIT_ADDRESS_SAVED'), 'AddressBook');
            $link = $this->gadget->urlMap('AddressBook');
            Jaws_Header::Location($link);
        }
    }

    /**
     * Fill and get combo with phone number, email address, address
     *
     * @access  public
     * @param   object  $tpl
     * @param   string  $base_block
     * @param   array   $inputValue
     * @param   integer $startIndex
     * @param   array   $options
     * @return  string  XHTML template content
     */
    function GetItemsCombo(&$tpl, $base_block, $inputValue, &$startIndex, $options, $seperatChar = ',')
    {
        if (trim($inputValue) == '') {
            return;
        }
        $inputValue = explode($seperatChar, trim($inputValue));

        foreach ($inputValue as $val) {
            $result = explode(':', $val);
            $tpl->SetBlock("address/$base_block");
            $tpl->SetVariable('icon_add', STOCK_ADD);
            $tpl->SetVariable('icon_remove', STOCK_REMOVE);
            $tpl->SetVariable('index', $startIndex);
            $tpl->SetVariable('value', $result[1]);
            foreach ($options as $key => $Info) {
                $tpl->SetBlock("address/$base_block/item");
                $tpl->SetVariable('type_id', $key);
                $tpl->SetVariable('type_name', _t('ADDRESSBOOK_' . $Info['lang']));
                $tpl->SetVariable('selected', ($key == $result[0])? 'selected="selected"': '');
                $tpl->ParseBlock("address/$base_block/item");
            }
            $tpl->ParseBlock("address/$base_block");
            $startIndex +=1;
        }
    }

    /**
     * Fill and get input with url
     *
     * @access  public
     * @param   object  $tpl
     * @param   string  $base_block
     * @param   array   $inputValue
     * @return  string  XHTML template content
     */
    function GetItemsInput(&$tpl, $base_block, $inputValue, &$startIndex)
    {
        foreach ($inputValue as $val) {
            $tpl->SetBlock("address/$base_block");
            $tpl->SetVariable('lbl_url',  _t('ADDRESSBOOK_ITEMS_URL'));
            $tpl->SetVariable('icon_add', STOCK_ADD);
            $tpl->SetVariable('icon_remove', STOCK_REMOVE);
            $tpl->SetVariable('index', $startIndex);
            $tpl->SetVariable('value', $val);
            $tpl->ParseBlock("address/$base_block");
            $startIndex +=1;
        }
    }

    /**
     * Delete Address
     *
     * @access  public
     */
    function DeleteAddress($ids = null)
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        if (empty($ids)) {
            $ids = jaws()->request->fetch('adr:array');
        }
        $link = $this->gadget->urlMap('AddressBook');

        if (empty($ids)) {
            Jaws_Header::Location($link);
            return false;
        }

        $model = $this->gadget->model->load('AddressBook');
        $result = $model->DeleteAddressSection($ids, (int) $GLOBALS['app']->Session->GetAttribute('user'));

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse($result->getMessage(), 'AddressBook', RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('ADDRESSBOOK_RESULT_DELETE_ADDRESS_COMPLETE'), 'AddressBook');
        }
        Jaws_Header::Location($link, 'AddressBook');
    }

    /**
     * Uploads the personal image
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UploadImage()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $res = Jaws_Utils::UploadFiles($_FILES,
                                       Jaws_Utils::upload_tmp_dir(),
                                       'gif,jpg,jpeg,png');
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type'    => 'notice',
                              'message' => $res['upload_image'][0]['host_filename']);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);            

        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

    /**
     * Returns personal image as stream data
     *
     * @access  public
     * @return  bool    True on success, false otherwise
     */
    function LoadImage()
    {
        $file = jaws()->request->fetch('file', 'get');

        $objImage = Jaws_Image::factory();
        if (!Jaws_Error::IsError($objImage)) {
            if (!empty($file)) {
                $file = preg_replace("/[^[:alnum:]_\.-]*/i", "", $file);
                $result = $objImage->load(Jaws_Utils::upload_tmp_dir(). '/'. $file, true);
                if (!Jaws_Error::IsError($result)) {
                    $result = $objImage->display();
                    if (!Jaws_Error::IsError($result)) {
                        return $result;
                    }
                }
                                return var_dump($file);
            }
        }

        return false;
    }
}