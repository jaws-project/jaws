<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class AddressBook_Actions_AddressBook extends Jaws_Gadget_HTML
{
    /**
     * Telephone Types
     * @var     array
     * @access  private
     */
    var $_TelTypes = array(
        1 => array('fieldType' => 'home', 'telType' => 'cell', 'lang' => 'HOME_TELL'),
        2 => array('fieldType' => 'home', 'telType' => 'mobile', 'lang' => 'HOME_MOBILE'),
        3 => array('fieldType' => 'home', 'telType' => 'fax', 'lang' => 'HOME_FAX'),
        4 => array('fieldType' => 'work', 'telType' => 'cell', 'lang' => 'WORK_TELL'),
        5 => array('fieldType' => 'work', 'telType' => 'mobile', 'lang' => 'WORK_MOBILE'),
        6 => array('fieldType' => 'work', 'telType' => 'fax', 'lang' => 'WORK_FAX'),
        7 => array('fieldType' => 'other', 'telType' => 'cell', 'lang' => 'OTHER_TELL'),
        8 => array('fieldType' => 'other', 'telType' => 'mobile', 'lang' => 'OTHER_MOBILE'),
        9 => array('fieldType' => 'other', 'telType' => 'fax', 'lang' => 'OTHER_FAX'),
    );

    /**
     * Email Types
     * @var     array
     * @access  private
     */
    var $_EmailTypes = array(
        1 => array('fieldType' => 'home', 'lang' => 'HOME_EMAIL'),
        2 => array('fieldType' => 'work', 'lang' => 'WORK_EMAIL'),
        3 => array('fieldType' => 'other', 'lang' => 'OTHER_EMAIL'),
    );

    /**
     * Address Types
     * @var     array
     * @access  private
     */
    var $_AdrTypes = array(
        1 => array('fieldType' => 'home', 'lang' => 'HOME_ADR'),
        2 => array('fieldType' => 'work', 'lang' => 'WORK_ADR'),
        3 => array('fieldType' => 'other', 'lang' => 'OTHER_ADR'),
    );

    /**
     * Displays the list of Address Book items, this items can filter by $gid(group ID) param.
     *
     * @access  public
     * $gid     Group ID
     * @return  string HTML content with menu and menu items
     */
    function AddressList()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

        $rqst = jaws()->request->fetch(array('uid', 'page'));

        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];
        $user = empty($rqst['uid'])? (int) $GLOBALS['app']->Session->GetAttribute('user') : $rqst['uid'];
        $limit = 2;

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        $user = $usrModel->GetUser($user);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return Jaws_HTTPError::Get(404);
        }

        $mine = ($user['id'] == $GLOBALS['app']->Session->GetAttribute('user'));
        $addressItems = $model->GetAddressList($user['id'], null, !$mine, $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($addressItems)) {
            return $addressItems->getMessage(); // TODO: Show intelligible message
        }
        $addressCount = $model->GetAddressListCount($user['id'], null, !$mine);

        $this->SetTitle(_t('ADDRESSBOOK_NAME'));
        $tpl = $this->gadget->loadTemplate('AddressList.html');

        $tpl->SetBlock("address_list");
        $tpl->SetVariable('title', _t('ADDRESSBOOK_NAME'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address_list/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address_list/response');
        }

        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");
            $tpl->SetVariable('name', $addressItem['name']);
            $tpl->SetVariable('view_url', $this->gadget->urlMap('View', array('id' => $addressItem['id'])));
            $tpl->SetVariable('title', $addressItem['title']);

            if ($mine) {
                $tpl->SetBlock('address_list/item1/action');
                $tpl->SetVariable('action_lbl', _t('GLOBAL_EDIT'));
                $tpl->SetVariable('action_url', $this->gadget->urlMap('EditAddress', array('id' => $addressItem['id'])));
                $tpl->ParseBlock('address_list/item1/action');

                $tpl->SetBlock('address_list/item1/action');
                $tpl->SetVariable('action_lbl', _t('GLOBAL_DELETE'));
                $tpl->SetVariable('action_url', $this->gadget->urlMap('DeleteAddress', array('id' => $addressItem['id'])));
                $tpl->ParseBlock('address_list/item1/action');
            }

            $tpl->ParseBlock("address_list/item1");
        }

        if ($mine) {
            $link = $this->gadget->urlMap('ManageGroups');
            $tpl->SetVariable('manage_groups_link', $link);
            $tpl->SetVariable('manage_groups', _t('ADDRESSBOOK_GROUPS_MANAGE'));

            $tpl->SetBlock('address_list/action_header');
            $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
            $tpl->ParseBlock('address_list/action_header');

            // Add New
            $tpl->SetBlock("address_list/actions");
            $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_ITEMS_ADD'));
            $link = $this->gadget->urlMap('AddAddress');
            $tpl->SetVariable('action_url', $link);
            $tpl->ParseBlock("address_list/actions");
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'address_list',
            $page,
            $limit,
            $addressCount,
            _t('ADDRESSBOOK_COUNT', 4),
            'AddressList',
            array('uid' => $user['username'])
        );

        $tpl->ParseBlock('address_list');

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
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->AddScriptLink('libraries/mootools/core.js');
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_ADD_NEW_TITLE'));
        $tpl = $this->gadget->loadTemplate('EditAddress.html');

        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_ADD_NEW_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', 0);
        $tpl->SetVariable('lastID', 1);
        $tpl->SetVariable('action', 'InsertItem');
        $tpl->SetVariable('lbl_name',         _t('ADDRESSBOOK_ITEMS_NAME'));
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

        $current_image = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/images/photo128px.png');
        $personImage =& Piwi::CreateWidget('Image', $current_image);
        $personImage->SetID('personImage');
        $tpl->SetVariable('image', $personImage->Get());

        $tels = array(':');
        $iIndex = 1;
        $this->GetItemsCombo($tpl, 'tel', $tels, $iIndex, $this->_TelTypes);
        $this->GetItemsCombo($tpl, 'email', $tels, $iIndex, $this->_EmailTypes);
        $this->GetItemsCombo($tpl, 'adr', $tels, $iIndex, $this->_AdrTypes);
        $this->GetItemsInput($tpl, 'url', array(''), $iIndex);

        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $gModel = $this->gadget->load('Model')->load('Model', 'Groups');
        $groupList = $gModel->GetGroups($user);

        foreach ($groupList as $gInfo) {
            $tpl->SetBlock('address/group');
            $tpl->SetVariable('lbl_group', $gInfo['name']);
            $tpl->SetVariable('gid', $gInfo['id']);
            $tpl->ParseBlock('address/group');
        }

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressList');
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
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int) jaws()->request->fetch('id');
        // TODO: Check this ID for Me, And Can I Edit Or View This?!
        if ($id == 0) {
            return false;
        }

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $info = $model->GetAddressInfo($id);
        if (Jaws_Error::IsError($info)) {
            return $info->getMessage(); // TODO: Show intelligible message
        }

        if (!isset($info)) {
            return Jaws_HTTPError::Get(404);
        }

        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user')) {
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->AddScriptLink('libraries/mootools/core.js');
        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        $tpl = $this->gadget->loadTemplate('EditAddress.html');
        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('action', 'UpdateItem');
        $tpl->SetVariable('lbl_name',       _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_title',      _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_nickname',   _t('ADDRESSBOOK_ITEMS_NICKNAME'));
        $tpl->SetVariable('lbl_url',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes',      _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_public',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('tel_title',      _t('ADDRESSBOOK_TEL_TITLE'));
        $tpl->SetVariable('email_title',    _t('ADDRESSBOOK_EMAIL_TITLE'));
        $tpl->SetVariable('adr_title',      _t('ADDRESSBOOK_ADR_TITLE'));
        $tpl->SetVariable('group_title',    _t('ADDRESSBOOK_GROUP_TITLE'));
        $tpl->SetVariable('other_details',  _t('ADDRESSBOOK_OTHER_DETAILS'));
        $tpl->SetVariable('name',           $info['name']);
        $tpl->SetVariable('title',          $info['title']);
        $tpl->SetVariable('nickname',       $info['nickname']);
        $tpl->SetVariable('url',            $info['url']);
        $tpl->SetVariable('notes',          $info['notes']);
        if ($info['public']) {
            $tpl->SetBlock('address/selected');
            $tpl->ParseBlock('address/selected');
        }

        if (empty($info['image'])) {
            $current_image = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/images/photo128px.png');
        } else {
            $current_image = $GLOBALS['app']->getDataURL() . "addressbook/image/" . $info['image'];
            $current_image .= !empty($info['updatetime']) ? "?" . $info['updatetime'] . "" : '';
        }
        $personImage =& Piwi::CreateWidget('Image', $current_image);
        $personImage->SetID('personImage');
        $tpl->SetVariable('image', $personImage->Get());

        // upload/delete image
        $tpl->SetVariable('lbl_upload_image', _t('ADDRESSBOOK_PERSON_IMAGE_UPLOAD'));
        $tpl->SetVariable('lbl_delete_image', _t('ADDRESSBOOK_PERSON_IMAGE_DELETE'));

        /////////////
        $iIndex = 1;
        if (trim($info['tel_home']) != '') {
            $tels = explode(',', $info['tel_home']);
            $this->GetItemsCombo($tpl, 'tel', $tels, $iIndex, $this->_TelTypes);
        }

        if (trim($info['tel_work']) != '') {
            $tels = explode(',', $info['tel_work']);
            $this->GetItemsCombo($tpl, 'tel', $tels, $iIndex, $this->_TelTypes);
        }

        if (trim($info['tel_other']) != '') {
            $tels = explode(',', $info['tel_other']);
            $this->GetItemsCombo($tpl, 'tel', $tels, $iIndex, $this->_TelTypes);
        }

        if ($iIndex == 1) {
            $tels = array(':');
            $this->GetItemsCombo($tpl, 'tel', $tels, $iIndex, $this->_TelTypes);
        }

        /////////////
        $iIndex = 1;
        if (trim($info['email_home']) != '') {
            $emails = explode(',', $info['email_home']);
            $this->GetItemsCombo($tpl, 'email', $emails, $iIndex, $this->_EmailTypes);
        }

        if (trim($info['email_work']) != '') {
            $emails = explode(',', $info['email_work']);
            $this->GetItemsCombo($tpl, 'email', $emails, $iIndex, $this->_EmailTypes);
        }

        if (trim($info['email_other']) != '') {
            $emails = explode(',', $info['email_other']);
            $this->GetItemsCombo($tpl, 'email', $emails, $iIndex, $this->_EmailTypes);
        }

        if ($iIndex == 1) {
            $emails = array(':');
            $this->GetItemsCombo($tpl, 'email', $emails, $iIndex, $this->_EmailTypes);
        }

        /////////////
        $iIndex = 1;
        if (trim($info['adr_home']) != '') {
            $adrs = explode(',', $info['adr_home']);
            $this->GetItemsCombo($tpl, 'adr', $adrs, $iIndex, $this->_AdrTypes);
        }

        if (trim($info['adr_work']) != '') {
            $adrs = explode(',', $info['adr_work']);
            $this->GetItemsCombo($tpl, 'adr', $adrs, $iIndex, $this->_AdrTypes);
        }

        if (trim($info['adr_other']) != '') {
            $adrs = explode(',', $info['adr_other']);
            $this->GetItemsCombo($tpl, 'adr', $adrs, $iIndex, $this->_AdrTypes);
        }

        if ($iIndex == 1) {
            $adrs = array(':');
            $this->GetItemsCombo($tpl, 'adr', $adrs, $iIndex, $this->_AdrTypes);
        }

        /////////////
        $iIndex = 1;
        if (trim($info['url']) != '') {
            $urls = explode('/n', $info['url']);
            $this->GetItemsInput($tpl, 'url', $urls, $iIndex);
        }

        if ($iIndex == 1) {
            $this->GetItemsCombo($tpl, 'url', array(''), $iIndex);
        }
        $tpl->SetVariable('lastID', $iIndex);

        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $gModel = $this->gadget->load('Model')->load('Model', 'Groups');
        $groupList = $gModel->GetGroups($user);

        $agModel = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
        $agData = $agModel->GetGroupIDs($info['id'], $user);
        $groupAux = array();
        foreach ($agData as $group) {
            $groupAux[] = $group['group'];
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

        $btnSave =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'));
        $btnSave->SetSubmit();
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('cancel_lbl', _t('GLOBAL_CANCEL'));
        $link = $this->gadget->urlMap('AddressList');
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
    function InsertItem()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'title', 'delete_image', 'url', 'notes', 'public'), 'post');

        $groupIDs = jaws()->request->fetch('groups:array');
        $tels = jaws()->request->fetch(array('tel_type:array', 'tel_number:array'), 'post');

        $post['[user]'] = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

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
        if (isset($adrs['adr_type'])) {
            foreach ($adrs['adr'] as $key => $adr) {
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
        $post['adr_home'] = implode(',', $adrHome);
        $post['adr_work'] = implode(',', $adrWork);
        $post['adr_other'] = implode(',', $adrOther);

        $urls = jaws()->request->fetch('url:array', 'post');
        $post['url'] = implode('/n', $urls);

        if (empty($post['delete_image'])) {
            $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), 'gif,jpg,jpeg,png');
            if (!empty($res) && !Jaws_Error::IsError($res)) {
                $post['image'] = $res['image'][0]['host_filename'];
            }
        } else {
            $post['image'] = '';
        }
        unset($post['delete_image']);

        $adrID = $model->InsertAddress($post);

        if (Jaws_Error::IsError($adrID)) {
            $GLOBALS['app']->Session->PushSimpleResponse($adrID->getMessage(), 'AddressBook');
            Jaws_Header::Referrer();
        } else {
            if (is_array($groupIDs) && count($groupIDs) > 0) {
                $agModel = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
                foreach ($groupIDs as $gid => $group) {
                    $agModel->AddGroupToAddress($adrID, $gid, $post['[user]']);
                }
            }

            $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_NEW_ADDRESS_SAVED'), 'AddressBook');
            $link = $this->gadget->urlMap('AddressList');
            Jaws_Header::Location($link);
        }
    }

    /**
     * Update Address Book Data.
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function UpdateItem()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $post = jaws()->request->fetch(array('name', 'title', 'nickname', 'delete_image',
                                    'url', 'notes', 'public', 'id'),
                              'post');

        $id = (int) $post['id'];
        unset($post['id']);
        $groupIDs = jaws()->request->fetch('groups:array');
        $tels = jaws()->request->fetch(array('tel_type:array', 'tel_number:array'), 'post');

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

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
        if (isset($adrs['adr_type'])) {
            foreach ($adrs['adr'] as $key => $adr) {
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
        $post['adr_home'] = implode(',', $adrHome);
        $post['adr_work'] = implode(',', $adrWork);
        $post['adr_other'] = implode(',', $adrOther);

        $urls = jaws()->request->fetch('url:array', 'post');
        $post['url'] = implode('/n', $urls);

        if (empty($post['delete_image'])) {
            $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), 'gif,jpg,jpeg,png');
            if (!empty($res) && !Jaws_Error::IsError($res)) {
                $post['image'] = $res['image'][0]['host_filename'];
            }
        } else {
            $post['image'] = '';
        }
        unset($post['delete_image']);

        $result = $model->UpdateAddress($id, $post);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'AddressBook');
            Jaws_Header::Referrer();
        } else {
            $agModel = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
            $agModel->DeleteGroupForAddress($id, $post['[user]']);
            if (is_array($groupIDs) && count($groupIDs) > 0) {
                foreach ($groupIDs as $gid => $group) {
                    $agModel->AddGroupToAddress($id, $gid, $user);
                }
            }

            $GLOBALS['app']->Session->PushSimpleResponse(_t('ADDRESSBOOK_RESULT_EDIT_ADDRESS_SAVED'), 'AddressBook');
            $link = $this->gadget->urlMap('AddressList');
            Jaws_Header::Location($link);
        }
    }

    /**
     * Get page navigation links
     *
     * @access  public
     * @param   object  $tpl
     * @param   string  $base_block
     * @param   int     $page       page number
     * @param   int     $page_size  Entries count per page
     * @param   int     $total      Total entries count
     * @param   string  $total_string
     * @param   string  $action     Action name
     * @param   array   $params     Action params array
     * @return  string  XHTML template content
     */
    function GetPagesNavigation(&$tpl, $base_block, $page, $page_size, $total,
                                $total_string, $action, $params = array())
    {
        $pager = $this->GetNumberedPagesNavigation($page, $page_size, $total);
        if (count($pager) > 0) {
            $tpl->SetBlock("$base_block/pager");
            $tpl->SetVariable('total', $total_string);

            foreach ($pager as $k => $v) {
                $tpl->SetBlock("$base_block/pager/item");
                $params['page'] = $v;
                if ($k == 'next') {
                    if ($v) {
                        $tpl->SetBlock("$base_block/pager/item/next");
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXTPAGE'));
                        $url = $this->gadget->urlMap($action, $params);
                        $tpl->SetVariable('url_next', $url);
                        $tpl->ParseBlock("$base_block/pager/item/next");
                    } else {
                        $tpl->SetBlock("$base_block/pager/item/no_next");
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXTPAGE'));
                        $tpl->ParseBlock("$base_block/pager/item/no_next");
                    }
                } elseif ($k == 'previous') {
                    if ($v) {
                        $tpl->SetBlock("$base_block/pager/item/previous");
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUSPAGE'));
                        $url = $this->gadget->urlMap($action, $params);
                        $tpl->SetVariable('url_previous', $url);
                        $tpl->ParseBlock("$base_block/pager/item/previous");
                    } else {
                        $tpl->SetBlock("$base_block/pager/item/no_previous");
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUSPAGE'));
                        $tpl->ParseBlock("$base_block/pager/item/no_previous");
                    }
                } elseif ($k == 'separator1' || $k == 'separator2') {
                    $tpl->SetBlock("$base_block/pager/item/page_separator");
                    $tpl->ParseBlock("$base_block/pager/item/page_separator");
                } elseif ($k == 'current') {
                    $tpl->SetBlock("$base_block/pager/item/page_current");
                    $url = $this->gadget->urlMap($action, $params);
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock("$base_block/pager/item/page_current");
                } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                    $tpl->SetBlock("$base_block/pager/item/page_number");
                    $url = $this->gadget->urlMap($action, $params);
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock("$base_block/pager/item/page_number");
                }
                $tpl->ParseBlock("$base_block/pager/item");
            }

            $tpl->ParseBlock("$base_block/pager");
        }
    }

    /**
     * Get numbered pages navigation
     *
     * @access  public
     * @param   int     $page      Current page number
     * @param   int     $page_size Entries count per page
     * @param   int     $total     Total entries count
     * @return  array   array with numbers of pages
     */
    function GetNumberedPagesNavigation($page, $page_size, $total)
    {
        $tail = 1;
        $paginator_size = 4;
        $pages = array();
        if ($page_size == 0) {
            return $pages;
        }

        $npages = ceil($total / $page_size);
        if ($npages < 2) {
            return $pages;
        }

        // Previous
        if ($page == 1) {
            $pages['previous'] = false;
        } else {
            $pages['previous'] = $page - 1;
        }

        if ($npages <= ($paginator_size + $tail)) {
            for ($i = 1; $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } elseif ($page < $paginator_size) {
            for ($i = 1; $i <= $paginator_size; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;
            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }
        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;
            for ($i = $npages - $paginator_size + ($tail - 1); $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } else {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;
            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;
            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }
        }

        // Next
        if ($page == $npages) {
            $pages['next'] = false;
        } else {
            $pages['next'] = $page + 1;
        }

        $pages['total'] = $total;
        return $pages;
    }

    /**
     * Displays not editable version of one address
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function View()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int) jaws()->request->fetch('id');
        // TODO: Check this ID for Me, And Can I Edit Or View This?!
        if ($id == 0) {
            return false;
        }

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
        $info = $model->GetAddressInfo($id);
        if (Jaws_Error::IsError($info)) {
            return $info->getMessage(); // TODO: Show intelligible message
        }

        if (!isset($info)) {
            return Jaws_HTTPError::Get(404);
        }

        if ($info['user'] != $GLOBALS['app']->Session->GetAttribute('user') && $info['public'] == false) {
            return Jaws_HTTPError::Get(403);
        }

        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        $tpl = $this->gadget->loadTemplate('ViewAddress.html');
        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_EDIT_TITLE'));
        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('action', 'UpdateItem');
        $tpl->SetVariable('lbl_name',     _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_nickname', _t('ADDRESSBOOK_ITEMS_NICKNAME'));
        $tpl->SetVariable('lbl_title',    _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_url',      _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes',    _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_public',   _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        $tpl->SetVariable('name',      $info['name']);
        $tpl->SetVariable('nickname',  $info['nickname']);
        $tpl->SetVariable('title',     $info['title']);
        $tpl->SetVariable('url',       $info['url']);
        $tpl->SetVariable('notes',     $info['notes']);

        if (trim($info['tel_home']) != '') {
            $tels = explode(',', $info['tel_home']);
            $this->GetItemsLable($tpl, 'item', $tels, $this->_TelTypes);
        }
        if (trim($info['tel_work']) != '') {
            $tels = explode(',', $info['tel_work']);
            $this->GetItemsLable($tpl, 'item', $tels, $this->_TelTypes);
        }
        if (trim($info['tel_other']) != '') {
            $tels = explode(',', $info['tel_other']);
            $this->GetItemsLable($tpl, 'item', $tels, $this->_TelTypes);
        }

        if ($info['public']) {
            $tpl->SetBlock('address/selected');
            $tpl->SetVariable('lbl_is_public',     _t('ADDRESSBOOK_ITEMS_IS_PUBLIC'));
            $tpl->ParseBlock('address/selected');
        }

        $agModel = $this->gadget->load('Model')->load('Model', 'AddressBookGroup');
        $agData = $agModel->GetData($info['id'], $info['user']);

        if (isset($agData)) {
            foreach ($agData as $gInfo) {
                $tpl->SetBlock('address/group');
                $tpl->SetVariable('lbl_group', $gInfo['name']);
                $tpl->ParseBlock('address/group');
            }
        }

        $tpl->SetBlock('address/actions');
        if ($info['user'] == $GLOBALS['app']->Session->GetAttribute('user')) {
            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_EDIT'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('EditAddress', array('id' => $info['id'])));
            $tpl->ParseBlock('address/actions/action');

            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_DELETE'));
            $tpl->ParseBlock('address/actions/action');

            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_MY'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressList'));
            $tpl->ParseBlock('address/actions/action');
        } else {
            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_MY'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressList'));
            $tpl->ParseBlock('address/actions/action');

            require_once JAWS_PATH . 'include/Jaws/User.php';
            $usrModel = new Jaws_User;
            $user = $usrModel->GetUser((int) $info['user']);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $tpl->SetBlock('address/actions/action');
                $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_USER'));
                $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressList', array('uid' => $user['username'])));
                $tpl->ParseBlock('address/actions/action');
            }
        }
        $tpl->ParseBlock('address/actions');

        $tpl->ParseBlock('address');

        return $tpl->Get();
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
    function GetItemsCombo(&$tpl, $base_block, $inputValue, &$startIndex, $options)
    {
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
     * Get lists of phone number, email address, address
     *
     * @access  public
     * @param   object  $tpl
     * @param   string  $base_block
     * @param   array   $inputValue
     * @param   array   $options
     * @return  string  XHTML template content
     */
    function GetItemsLable(&$tpl, $base_block, $inputValue, $options)
    {
        foreach ($inputValue as $val) {
            $result = explode(':', $val);
            $tpl->SetBlock("address/$base_block");
            $tpl->SetVariable('item', $result[1]);
            $tpl->SetVariable('lbl_item', _t('ADDRESSBOOK_' . $options[$result[0]]['lang']));
            $tpl->ParseBlock("address/$base_block");
        }
    }
}