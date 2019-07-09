<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Account_Github_Logout extends Users_Account_Github
{
    /**
     * Logout
     *
     * @access  public
     * @return  void
     */
    function Logout()
    {
        $revokeURL = sprintf(
            $this->revokeURL,
            $this->ClientID,
            $this->gadget->session->fetch('access_token')
        );
        $httpRequest = new Jaws_HTTPRequest();
        $httpRequest->httpRequest->setHeader(
            'Authorization',
            'Basic ' .base64_encode($this->ClientID.':'.$this->ClientSecret)
        );
        $result = $httpRequest->delete($revokeURL);
        if (Jaws_Error::IsError($result) || $result['status'] != 204) {
            return Jaws_Error::raiseError('Revoke token error!', __FUNCTION__);
        }

        return true;
    }

}