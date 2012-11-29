<?php
/**
 * Preferences Gadget Model
 *
 * @category   GadgetModel
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PreferencesModel extends Jaws_Model
{
    /**
     * Save the cookie, save the world
     *
     * @access  public
     * @param   array   $Preferences
     * @param   int     $expire_age
     * @return  bool    True/False
     */
    function SavePreferences($preferences, $expire_age = 1440)
    {
        $preferences = array_filter($preferences);
        $GLOBALS['app']->Session->SetCookie('preferences', serialize($preferences), $expire_age);
        return true;
    }

}