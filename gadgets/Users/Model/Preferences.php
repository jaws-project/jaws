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
     * Updates user profile
     *
     * @access  public
     * @param   int     $uid        User ID
     * @param   string  $language   User language
     * @param   string  $theme      User theme
     * @param   string  $editor     User editor
     * @param   string  $timezone   User timezone
     * @return  mixed   True on success or Jaws_Error on failure
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