<?php
/**
 * Preferences AJAX API
 *
 * @category   Ajax
 * @package    Preferences
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PreferencesAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Update preferences
     *
     * @access  public
     * @param   array   $preferences_config
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences($preferences_config)
    {
        $this->CheckSession('Preferences', 'UpdateProperties');
        $this->_Model->UpdatePreferences($preferences_config);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}
