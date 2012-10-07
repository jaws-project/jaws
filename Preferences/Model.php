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
     * @param   int     $expiretime
     * @return  boolean True/False
     */
    function SavePreferences($Preferences, $expire_age = 1440)
    {
        foreach ($Preferences as $key => $value) {
            if ($value == 'false') {
                $GLOBALS['app']->Session->DestroyCookie($key);
            } else {
                $GLOBALS['app']->Session->SetCookie($key, $value, $expire_age);
            }
        }
        return true;
    }
}