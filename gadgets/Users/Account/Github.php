<?php
/**
 * Github authentication class
 *
 * @category   Auth
 * @package    Core
 */
class Users_Account_Github extends Jaws_Gadget_Action
{
    /**
     * OAuth2 Client ID
     *
     * @var     string
     * @access  protected
     */
    protected $ClientID = '';

    /**
     * OAuth2 Client secret
     *
     * @var     string
     * @access  protected
     */
    protected $ClientSecret = '';

    /**
     * OAuth2 server authorize URL
     *
     * @var     string
     * @access  protected
     */
    protected $authorizeURL = 'https://github.com/login/oauth/authorize';

    /**
     * OAuth2 server token URL
     *
     * @var     string
     * @access  protected
     */
    protected $tokenURL = 'https://github.com/login/oauth/access_token';

    /**
     * OAuth2 server revoke URL
     *
     * @var     string
     * @access  protected
     */
    protected $revokeURL = 'https://api.github.com/applications/%s/tokens/%s';

    /**
     * OAuth2 server api base URL
     *
     * @var     string
     * @access  protected
     */
    protected $apiBaseURL = 'https://api.github.com/';

}