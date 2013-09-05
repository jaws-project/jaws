<?php
/**
 * Preferences AJAX API
 *
 * @category   Ajax
 * @package    Preferences
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Update preferences
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences()
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $preferences_config = jaws()->request->getAll('post');
        $modelPreferences = $this->gadget->load('Model')->load('AdminModel');
        $modelPreferences->UpdatePreferences($preferences_config);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}