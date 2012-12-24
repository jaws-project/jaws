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
class Tms_AdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   string  $model  TMS model
     * @return  void
     */
    function TmsAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Gets information of given theme
     *
     * @access  public
     * @param   string  $theme  Name of the theme
     * @return  array   Theme info
     */
    function GetThemeInfo($theme)
    {
        $html = $GLOBALS['app']->LoadGadget('Tms', 'AdminHTML');
        return $html->GetThemeInfo($theme);
    }

}