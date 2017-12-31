<?php
/**
 * IMAP authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_IMAP
{
    /**
     * IMAP server
     *
     * @var     string
     * @access  private
     */
    private $_Server = 'localhost';

    /**
     * IMAP port
     *
     * @var     string
     * @access  private
     */
    private $_Port = '143';

    /**
     * Using SSL
     *
     * @var     bool
     * @access  private
     */
    private $_SSL = false;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->_Server = 'localhost';
        $this->_Port   = '143';
        $this->_SSL    = false;
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   array   $loginData  Login data(username, password, ...)
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($loginData)
    {
        if (!function_exists('imap_open')) {
            return Jaws_Error::raiseError(
                'Undefined function imap_open()',
                __FUNCTION__
            );
        }

        if ($loginData['usecrypt']) {
            $JCrypt = Jaws_Crypt::getInstance();
            if (!Jaws_Error::IsError($JCrypt)) {
                $loginData['password'] = $JCrypt->decrypt($loginData['password']);
            }
        } else {
            $loginData['password'] = Jaws_XSS::defilter($loginData['password']);
        }

        $mbox = @imap_open(
            '{'.$this->_Server.':'.$this->_Port.($this->_SSL?'/imap/ssl':'').'}INBOX',
            $loginData['username'],
            $loginData['password']
        );
        if ($mbox) {
            @imap_close($mbox);
            $result = array();
            $result['id']         = strtolower('imap:'.$loginData['username']);
            $result['internal']   = false;
            $result['username']   = $loginData['username'];
            $result['superadmin'] = false;
            $result['internal']   = false;
            $result['groups']     = array();
            $result['nickname']   = $loginData['username'];
            $result['concurrents'] = 0;
            $result['email']      = '';
            $result['url']        = '';
            $result['avatar']     = 'gadgets/Users/Resources/images/photo48px.png';
            $result['language']   = '';
            $result['theme']      = '';
            $result['editor']     = '';
            $result['timezone']   = null;
            return $result;
        }

        return Jaws_Error::raiseError(
            _t('GLOBAL_ERROR_LOGIN_WRONG'),
            __FUNCTION__
        );
    }

}