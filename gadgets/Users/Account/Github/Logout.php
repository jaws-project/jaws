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
        $httpRequest->content_type = 'application/x-www-form-urlencoded';
        $httpRequest->httpRequest->setHeader('Accept', 'application/json');
        $httpRequest->httpRequest->setHeader(
            'Authorization',
            'Basic ' .base64_encode($this->ClientID.':'.$this->ClientSecret)
        );
        $result = $httpRequest->delete($revokeURL, $responseData);
        if (Jaws_Error::IsError($result) || $result != 200) {
            return Jaws_Error::raiseError('Revoke token error!', __FUNCTION__);
        }

        return true;
    }

}