<?php
/**
 * TMS (Theme Management System) AJAX API
 *
 * @category   Ajax
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class TmsAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function TmsAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Get information of a given theme
     *
     * @access  public
     * @param   string  $theme      Theme's name
     * @return  array   Theme's info
     */
    function GetThemeInfo($theme)
    {
        $html = $GLOBALS['app']->LoadGadget('Tms', 'AdminHTML');
        return $html->GetThemeInfo($theme);
    }

}