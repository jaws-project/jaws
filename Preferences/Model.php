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
class PreferencesModel extends Jaws_Gadget_Model
{
    /**
     * Save the cookie, save the world
     *
     * @access  public
     * @param   array   $Preferences
     * @param   int     $expire_age
     * @return  bool    True/False
     */
    function SavePreferences($Preferences, $expire_age = 1440)
    {
        $Preferences = array_filter($Preferences);
        $GLOBALS['app']->Session->SetCookie('preferences', serialize($Preferences), $expire_age);

        return true;
    }
}