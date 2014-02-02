<?php
/**
 * POP3 authentication class
 *
 * @category   Auth
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Auth_POP3
{
    /**
     * POP3 server
     * @access  private
     */
    private $_Server = 'localhost';

    /**
     * POP3 port
     * @access  private
     */
    private $_Port = '110';

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function Jaws_Auth_POP3()
    {
        $this->_Server = 'localhost';
        $this->_Port   = '110';
    }

    /**
     * Authenticate user/password
     *
     * @access  public
     * @param   string  $user       User's name or email
     * @param   string  $password   User's password
     * @return  mixed   Array of user's information otherwise Jaws_Error
     */
    function Auth($user, $password)
    {
        if (!function_exists('imap_open')) {
            return Jaws_Error::raiseError(
                'Undefined function imap_open()',
                __FUNCTION__
            );
        }

        $mbox = @imap_open(
            '{'.$this->_Server.'/pop3:'.$this->_Port.'/notls}INBOX',
            $user,
            $password
        );
        if ($mbox) {
            @imap_close($mbox);
            $result = array();
            $result['id']         = strtolower('pop3:'.$user);
            $result['internal']   = false;
            $result['username']   = $user;
            $result['superadmin'] = false;
            $result['internal']   = false;
            $result['groups']     = array();
            $result['nickname']   = $user;
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