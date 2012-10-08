<?php
/**
 * IMAP authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_IMAP
{
    /**
     * Authentication ID
     * @access  private
     */
    var $_AuthID = '';

    /**
     * username
     * @access  private
     */
    var $_User = '';

    /**
     * IMAP server
     * @access  private
     */
    var $_Server = 'localhost';

    /**
     * IMAP port
     * @access  private
     */
    var $_Port = '143';

    /**
     * Using SSL
     * @access  private
     */
    var $_SSL = false;

    /**
     * Constructor
     *
     * @access  public
     */
    function Jaws_Auth_IMAP()
    {
        $this->_Server = 'localhost';
        $this->_Port   = '143';
        $this->_SSL    = false;
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     */
    function Auth($user, $password)
    {
        if (!function_exists('imap_open')) {
            return Jaws_Error::raiseError('Undefined function imap_open()',
                                          __FUNCTION__);
        }

        $mbox = @imap_open('{'.$this->_Server.':'.$this->_Port.($this->_SSL?'/imap/ssl':'').'}INBOX',
                           $user,
                           $password);
        if ($mbox) {
            @imap_close($mbox);
            $this->_User   = $user;
            $this->_AuthID = strtolower('imap:'.$user);
            return $this->_AuthID; 
        }

        return Jaws_Error::raiseError(_t('GLOBAL_ERROR_LOGIN_WRONG'),
                                         __FUNCTION__);
    }

    /**
     * Attributes of logged user
     *
     * @access  public
     */
    function GetAttributes()
    {
        $info = array();
        $info['id']         = $this->_AuthID;
        $info['internal']   = false;
        $info['username']   = $this->_User;
        $info['superadmin'] = false;
        $info['internal']   = false;
        $info['groups']     = array();
        $info['nickname']   = $this->_User;
        $info['concurrent_logins'] = 0;
        $info['email']      = '';
        $info['url']        = '';
        $info['avatar']     = 'gadgets/Users/images/no-photo.png';
        $info['language']   = '';
        $info['theme']      = '';
        $info['editor']     = '';
        $info['timezone']   = null;
        return $info;
    }

}