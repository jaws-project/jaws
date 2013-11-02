<?php
require_once JAWS_PATH. 'gadgets/AddressBook/Actions/Default.php';
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_ViewAddress extends AddressBook_Actions_Default
{
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

        $model = $this->gadget->loadModel('AddressBook');
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

        $this->SetTitle(_t('ADDRESSBOOK_ITEMS_VIEW_TITLE'));
        $tpl = $this->gadget->loadTemplate('ViewAddress.html');
        $tpl->SetBlock("address");
        $tpl->SetVariable('top_title', _t('ADDRESSBOOK_ITEMS_VIEW_TITLE'));
        $tpl->SetVariable('id', $info['id']);
        $tpl->SetVariable('action', 'UpdateAddress');
        $tpl->SetVariable('lbl_name0',    _t('ADDRESSBOOK_ITEMS_LASTNAME'));
        $tpl->SetVariable('lbl_name1',    _t('ADDRESSBOOK_ITEMS_FIRSTNAME'));
        $tpl->SetVariable('lbl_nickname', _t('ADDRESSBOOK_ITEMS_NICKNAME'));
        $tpl->SetVariable('lbl_title',    _t('ADDRESSBOOK_ITEMS_TITLE'));
        $tpl->SetVariable('lbl_notes',    _t('ADDRESSBOOK_ITEMS_NOTES'));
        $tpl->SetVariable('nickname',  $info['nickname']);
        $tpl->SetVariable('title',     $info['title']);
        $tpl->SetVariable('notes',     $info['notes']);

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

        // Tel
        $this->GetItemsLable($tpl, 'item', $info['tel_home'], $this->_TelTypes);
        $this->GetItemsLable($tpl, 'item', $info['tel_work'], $this->_TelTypes);
        $this->GetItemsLable($tpl, 'item', $info['tel_other'], $this->_TelTypes);

        // Email
        $this->GetItemsLable($tpl, 'item', $info['email_home'], $this->_EmailTypes);
        $this->GetItemsLable($tpl, 'item', $info['email_work'], $this->_EmailTypes);
        $this->GetItemsLable($tpl, 'item', $info['email_other'], $this->_EmailTypes);

        // URL
        $this->GetItemsLable($tpl, 'item', $info['url'], null, '\n');

        if ($info['public']) {
            $tpl->SetBlock('address/selected');
            $tpl->SetVariable('lbl_is_public',     _t('ADDRESSBOOK_ITEMS_IS_PUBLIC'));
            $tpl->ParseBlock('address/selected');
        }

        $agModel = $this->gadget->loadModel('AddressBookGroup');
        $agData = $agModel->GetData($info['id'], $info['user']);

        if (isset($agData)) {
            foreach ($agData as $gInfo) {
                $tpl->SetBlock('address/group');
                $tpl->SetVariable('lbl_group', $gInfo['name']);
                $tpl->ParseBlock('address/group');
            }
        }

        $tpl->SetVariable('menubar', $this->MenuBar(''));

        $tpl->SetBlock('address/actions');
        if ($info['user'] == $GLOBALS['app']->Session->GetAttribute('user')) {
            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('GLOBAL_EDIT'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('EditAddress', array('id' => $info['id'])));
            $tpl->ParseBlock('address/actions/action');
        } else {
            $usrModel = new Jaws_User;
            $user = $usrModel->GetUser((int) $info['user']);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $tpl->SetBlock('address/actions/action');
                $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_USER'));
                $tpl->SetVariable('action_url', $this->gadget->urlMap('UserAddress', array('uid' => $user['username'])));
                $tpl->ParseBlock('address/actions/action');
            }
        }
        $tpl->ParseBlock('address/actions');

        $tpl->ParseBlock('address');

        return $tpl->Get();
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
    function GetItemsLable(&$tpl, $base_block, $inputValue, $options = null, $seperatChar = ',')
    {
        if (trim($inputValue) == '') {
            return;
        }
        $inputValue = explode($seperatChar, trim($inputValue));

        foreach ($inputValue as $val) {
            $tpl->SetBlock("address/$base_block");
            if (isset($options)) {
                $result = explode(':', $val);
                $tpl->SetVariable('item', $result[1]);
                $tpl->SetVariable('lbl_item', _t('ADDRESSBOOK_' . $options[$result[0]]['lang']));
            } else {
                $tpl->SetVariable('item', $val);
                $tpl->SetVariable('lbl_item', _t('ADDRESSBOOK_ITEMS_URL'));
            }
            $tpl->ParseBlock("address/$base_block");
        }
    }
}