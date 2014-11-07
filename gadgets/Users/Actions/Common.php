<?php
/**
 * Users Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Users_Actions_Common extends Jaws_Gadget_Action
{
    /**
     * Displays user's menu bar
     *
     * @access  public
     * @param   string  $selected    selected action
     * @return  string XHTML template content
     */
    function MenuBar($selected)
    {
        $actions = array('Account', 'Personal', 'Preferences', 'Contacts');
        if (!in_array($selected, $actions)) {
            $selected = 'Account';
        }

        $menubar = new Jaws_Widgets_Menubar();

        if ($this->gadget->GetPermission('EditUserName,EditUserNickname,EditUserEmail,EditUserPassword', '', false)) {
            $menubar->AddOption('Account',
                _t('USERS_EDIT_ACCOUNT'),
                $this->gadget->urlMap('Account'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserPersonal')) {
            $menubar->AddOption('Personal',
                _t('USERS_EDIT_PERSONAL'),
                $this->gadget->urlMap('Personal'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserPreferences')) {
            $menubar->AddOption('Preferences',
                _t('USERS_EDIT_PREFERENCES'),
                $this->gadget->urlMap('Preferences'),
                STOCK_EDIT);
        }

        if ($this->gadget->GetPermission('EditUserContacts')) {
            $menubar->AddOption('Contacts',
                _t('USERS_EDIT_CONTACTS'),
                $this->gadget->urlMap('Contacts'),
                STOCK_EDIT);
        }

        $menubar->Activate($selected);

        return $menubar->Get();
    }
}