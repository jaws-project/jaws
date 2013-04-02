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
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Preferences_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Update preferences
     *
     * @access  public
     * @param   array   $preferences_config
     * @return  array   Response array (notice or error)
     */
    function UpdatePreferences($preferences_config)
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->_Model->UpdatePreferences($preferences_config);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}