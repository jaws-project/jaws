<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Share extends Jaws_Gadget_HTML
{
    /**
     * Fetches list of system user groups
     *
     * @access  public
     * @return  array   Array of groups or an empty array
     */
    function GetShareForm()
    {
        $tpl = $this->gadget->loadTemplate('Share.html');
        $tpl->SetBlock('share');

        // Edit UI
        if ($this->gadget->GetPermission('ShareFile')) {
            $tpl->SetBlock('share/edit');
            require_once JAWS_PATH . 'include/Jaws/User.php';
            $uModel = new Jaws_User();
            $groups = $uModel->GetGroups(true, 'title');
            if (!Jaws_Error::IsError($groups)) {
                $combo =& Piwi::CreateWidget('Combo', 'groups');
                $combo->AddEvent(ON_CHANGE, 'toggleUsers(this.value)');
                $combo->AddOption('All Users', 0);
                foreach ($groups as $group) {
                    $combo->AddOption($group['title'], $group['id']);
                }
                $tpl->SetVariable('groups', $combo->Get());
            }
            $tpl->ParseBlock('share/edit');
            $tpl->SetBlock('share/actions');
            $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
            $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
            $tpl->ParseBlock('share/actions');
        }

        $tpl->ParseBlock('share');
        return $tpl->Get();
    }

    /**
     * Fetches list of system users
     *
     * @access  public
     * @return  array   Array of users or an empty array
     */
    function GetUsers()
    {
        $gid = (int)jaws()->request->fetch('gid');
        if ($gid === 0) {
            $gid = false;
        }
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $users = $uModel->GetUsers($gid, null, 1);
        //$users = $uModel->GetUsers();
        if (Jaws_Error::IsError($users)) {
            return array();
        }
        return $users;
    }

    /**
     * Fetches ID's of users whitch the file is shared for
     *
     * @access  public
     * @return  array   Array of users or an empty array
     */
    function GetFileUsers()
    {
        $fid = (int)jaws()->request->fetch('fid');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Share');
        $ids = $model->GetFileUsers($fid);
        if (Jaws_Error::IsError($ids)) {
            return array();
        }
        return $ids;
    }

}