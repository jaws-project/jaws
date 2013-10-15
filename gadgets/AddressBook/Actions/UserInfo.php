<?php
/**
 * AddressBook Gadget
 *
 * @category   GadgetAdmin
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/AddressBook/Resources/site_style.css');
class AddressBook_Actions_UserInfo extends Jaws_Gadget_HTML
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function LoadUserInfo()
    {
        $uid = (int) jaws()->request->fetch('uid');
        $uModel = new Jaws_User();
        $userInfo = $uModel->GetUser($uid, true, true);
        $userInfo['avatar_file_name'] = '';
        if (empty($userInfo['avatar'])) {
            $userInfo['avatar'] = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/Resources/images/photo128px.png');
        } else {
            $userAvatar = $GLOBALS['app']->getDataURL(). 'avatar/'. $userInfo['avatar'];
            copy($userAvatar, Jaws_Utils::upload_tmp_dir() . '/' . $userInfo['avatar']);
            $userInfo['avatar_file_name'] = $userInfo['avatar'];
            $userInfo['avatar'] = $GLOBALS['app']->getDataURL(). 'avatar/'. $userInfo['avatar'];
        }
        return $userInfo;
    }
}