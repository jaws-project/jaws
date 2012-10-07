<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Preferences extends Jaws_Model
{
    /**
     * Updates the profile of an user
     *
     * @access  public
     * @param   int      $uid       User's ID
     * @param   string   $username  Username
     * @param   string   $name      User's real name
     * @param   string   $email     User's email
     * @param   string   $url       User's url
     * @param   string   $password  Password
     * @param   boolean  $uppass    Really updte the user password?
     * @return  mixed    True (Success) or Jaws_Error (failure)
     */
    function UpdatePreferences($uid, $language, $theme, $editor, $timezone)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser    = new Jaws_User;
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $result = $jUser->UpdateAdvancedOptions($uid, array('language' => $language, 
                                                            'theme' => $theme, 
                                                            'editor' => $editor, 
                                                            'timezone' => $timezone)); 
        //TODO: catch error
        return $result;
    }

}