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
     * Displays the list of Address Book items, this items can filter by $gid(group ID) param.
     *
     * @access  public
     * $gid     Group ID
     * @return  string HTML content with menu and menu items
     */
    function AddressList()
    {
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('uid', 'page'));

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
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_phone', _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));

        foreach ($addressItems as $addressItem) {
            $tpl->SetBlock("address_list/item1");
            $tpl->SetVariable('name', $addressItem['name']);
            $tpl->SetVariable('view_url', $this->gadget->urlMap('View', array('id' => $addressItem['id'])));
            $tpl->SetVariable('company', $addressItem['company']);
            $tpl->SetVariable('title', $addressItem['title']);
            $tpl->SetVariable('email', $addressItem['email']);
            $tpl->SetVariable('phone', $addressItem['phone_number']);

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
        $tpl->SetVariable('action', 'InsertItem');
        $tpl->SetVariable('lbl_name', _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('lbl_company', _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('lbl_email', _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('lbl_phone', _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));
        $tpl->SetVariable('lbl_fax', _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('lbl_mobile', _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('lbl_address', _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('lbl_pstcode', _t('ADDRESSBOOK_ITEMS_POSTAL_CODE'));
        $tpl->SetVariable('lbl_url', _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('lbl_notes', _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('lbl_title', _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_public',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));

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

        $request =& Jaws_Request::getInstance();
        $id = (int) $request->get('id');
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
        $tpl->SetVariable('name',           $info['name']);
        $tpl->SetVariable('lbl_company',    _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('company',        $info['company']);
        $tpl->SetVariable('lbl_title',      _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('title',          $info['title']);
        $tpl->SetVariable('lbl_email',      _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('email',          $info['email']);
        $tpl->SetVariable('lbl_phone',      _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));
        $tpl->SetVariable('phone',          $info['phone_number']);
        $tpl->SetVariable('lbl_fax',        _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('fax',            $info['fax_number']);
        $tpl->SetVariable('lbl_mobile',     _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('mobile',         $info['mobile_number']);
        $tpl->SetVariable('lbl_address',    _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('address',        $info['address']);
        $tpl->SetVariable('lbl_pstcode',    _t('ADDRESSBOOK_ITEMS_POSTAL_CODE'));
        $tpl->SetVariable('pstcode',        $info['postal_code']);
        $tpl->SetVariable('lbl_url',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('url',            $info['url']);
        $tpl->SetVariable('lbl_notes',      _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('notes',          $info['notes']);
        $tpl->SetVariable('lbl_public',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));
        if ($info['public']) {
            $tpl->SetBlock('address/selected');
            $tpl->ParseBlock('address/selected');
        }

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

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('name', 'company', 'title',
                                    'email', 'phone_number', 'mobile_number',
                                    'fax_number', 'address', 'postal_code',
                                    'url', 'notes', 'public'),
                              'post');

        $groupIDs = $request->get('groups');
        $post['[user]'] = (int) $GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

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

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('name', 'company', 'title',
                                    'email', 'phone_number', 'mobile_number',
                                    'fax_number', 'address', 'postal_code',
                                    'url', 'notes', 'public', 'id'),
                              'post');

        $id = (int) $post['id'];
        unset($post['id']);

        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');

        // Check user edit His addressBook
        $addressInfo = $model->GetAddressInfo($id);
        $user = (int) $GLOBALS['app']->Session->GetAttribute('user');
        if (Jaws_Error::IsError($addressInfo) || !isset($addressInfo) || ($user != $addressInfo['user'])) {
            return Jaws_HTTPError::Get(403);
        }

        $groupIDs = $request->get('groups');
        $model = $this->gadget->load('Model')->load('Model', 'AddressBook');
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

        $request =& Jaws_Request::getInstance();
        $id = (int) $request->get('id');
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
        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('AddressBook')) {
            $tpl->SetBlock('address/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('address/response');
        }

        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('action', 'UpdateItem');
        $tpl->SetVariable('lbl_name',       _t('ADDRESSBOOK_ITEMS_NAME'));
        $tpl->SetVariable('name',           $info['name']);
        $tpl->SetVariable('lbl_company',    _t('ADDRESSBOOK_ITEMS_COMPANY'));
        $tpl->SetVariable('company',        $info['company']);
        $tpl->SetVariable('lbl_title',      _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('title',          $info['title']);
        $tpl->SetVariable('lbl_email',      _t('ADDRESSBOOK_ITEMS_EMAIL'));
        $tpl->SetVariable('email',          $info['email']);
        $tpl->SetVariable('lbl_phone',      _t('ADDRESSBOOK_ITEMS_PHONE_NUMBER'));
        $tpl->SetVariable('phone',          $info['phone_number']);
        $tpl->SetVariable('lbl_fax',        _t('ADDRESSBOOK_ITEMS_FAX'));
        $tpl->SetVariable('fax',            $info['fax_number']);
        $tpl->SetVariable('lbl_mobile',     _t('ADDRESSBOOK_ITEMS_MOBILE'));
        $tpl->SetVariable('mobile',         $info['mobile_number']);
        $tpl->SetVariable('lbl_address',    _t('ADDRESSBOOK_ITEMS_ADDRESS'));
        $tpl->SetVariable('address',        $info['address']);
        $tpl->SetVariable('lbl_pstcode',    _t('ADDRESSBOOK_ITEMS_POSTAL_CODE'));
        $tpl->SetVariable('pstcode',        $info['postal_code']);
        $tpl->SetVariable('lbl_url',        _t('ADDRESSBOOK_ITEMS_URL'));
        $tpl->SetVariable('url',            $info['url']);
        $tpl->SetVariable('lbl_notes',      _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('notes',          $info['notes']);
        $tpl->SetVariable('lbl_public',     _t('ADDRESSBOOK_ITEMS_PUBLIC'));
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
}

















