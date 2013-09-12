<?php
/**
 * AddressBook  AJAX API
 *
 * @category   Ajax
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
class AddressBook_Ajax extends Jaws_Gadget_HTML
{
    /**
     * Get user information
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetUserInfo()
    {
        $uid = (int) jaws()->request->fetch('uid');
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $uModel = new Jaws_User();
        $userInfo = $uModel->GetUser($uid, true, true);
        if (empty($userInfo['avatar'])) {
            $userInfo['avatar'] = $GLOBALS['app']->getSiteURL('/gadgets/Users/images/photo128px.png');
        } else {
            $userInfo['avatar'] = $GLOBALS['app']->getDataURL(). 'avatar/'. $userInfo['avatar'];
        }
        return $userInfo;
    }

}