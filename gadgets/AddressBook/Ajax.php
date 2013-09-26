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
        $uModel = new Jaws_User();
        $userInfo = $uModel->GetUser($uid, true, true);
        if (empty($userInfo['avatar'])) {
            $userInfo['avatar'] = $GLOBALS['app']->getSiteURL('/gadgets/AddressBook/images/photo128px.png');
        } else {
            $userInfo['avatar'] = $GLOBALS['app']->getDataURL(). 'avatar/'. $userInfo['avatar'];
        }
        return $userInfo;
    }

    /**
     * Filter AddressBook and return result
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function FilterAddress()
    {
        $rqst = jaws()->request->fetch(array('gid:int', 'term'));
        $gadgetHTML = $GLOBALS['app']->LoadGadget('AddressBook', 'HTML', 'AddressBook');
        return $gadgetHTML->AddressList((int) $rqst['gid'], $rqst['term']);
    }

    /**
     * Generate download link for vCard format
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetVCardDownloadLink()
    {
        $rqst = jaws()->request->fetch(array('gid:int', 'term'));
        return $this->gadget->urlMap('VCardBuild', array('group' => $rqst['gid'], 'term' => $rqst['term']));
    }

    function DeleteAddress()
    {
        //return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        return $GLOBALS['app']->Session->GetResponse('afasfasf', RESPONSE_ERROR);
    }

    /**
     * Copy user avatar to tmp folder
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CopyUserAvatar()
    {       
        $uid = (int) jaws()->request->fetch('uid');
        $uModel = new Jaws_User();
        $userInfo = $uModel->GetUser($uid);
        if (empty($userInfo['avatar'])) {
            $response = '';
        } else {
            $userAvatar = $GLOBALS['app']->getDataURL(). 'avatar/'. $userInfo['avatar'];
            copy($userAvatar, Jaws_Utils::upload_tmp_dir() . '/' . $userInfo['avatar']);
            $response = $userInfo['avatar'];
        }
        return $response;
    }

    function VCardBuild()
    {
        $ids = jaws()->request->fetch('adr:array');
        $gadgetHTML = $GLOBALS['app']->LoadGadget('AddressBook', 'HTML', 'VCardBuild');
        return $gadgetHTML->VCardBuild($ids);
    }
}