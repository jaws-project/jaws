<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/resources/site_style.css');
class AddressBook_Actions_ViewAddress extends AddressBook_HTML
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
            $current_image = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/images/photo128px.png');
        } else {
            $current_image = $GLOBALS['app']->getDataURL() . "addressbook/image/" . $info['image'];
            $current_image .= !empty($info['updatetime']) ? "?" . $info['updatetime'] . "" : '';
        }
        $tpl->SetVariable('image_src', $current_image);

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

        //////
        if (trim($info['email_home']) != '') {
            $emails = explode(',', $info['email_home']);
            $this->GetItemsLable($tpl, 'item', $emails, $this->_EmailTypes);
        }
        if (trim($info['email_work']) != '') {
            $tels = explode(',', $info['email_work']);
            $this->GetItemsLable($tpl, 'item', $emails, $this->_EmailTypes);
        }
        if (trim($info['email_other']) != '') {
            $tels = explode(',', $info['email_other']);
            $this->GetItemsLable($tpl, 'item', $emails, $this->_EmailTypes);
        }

        if (trim($info['url']) != '') {
            $urls = explode('/n', $info['url']);
            $this->GetItemsLable($tpl, 'item', $urls);
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
            $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_MY'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressBook'));
            $tpl->ParseBlock('address/actions/action');
        } else {
            $tpl->SetBlock('address/actions/action');
            $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_MY'));
            $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressBook'));
            $tpl->ParseBlock('address/actions/action');

            $usrModel = new Jaws_User;
            $user = $usrModel->GetUser((int) $info['user']);
            if (!Jaws_Error::IsError($user) && !empty($user)) {
                $tpl->SetBlock('address/actions/action');
                $tpl->SetVariable('action_lbl', _t('ADDRESSBOOK_VIEW_ALL_ADDREESS_USER'));
                $tpl->SetVariable('action_url', $this->gadget->urlMap('AddressBook', array('uid' => $user['username'])));
                $tpl->ParseBlock('address/actions/action');
            }
        }
        $tpl->ParseBlock('address/actions');

        $tpl->ParseBlock('address');

        return $tpl->Get();
    }
}