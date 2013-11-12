<?php
/**
 * TMS (Theme Management System) AJAX API
 *
 * @category   Ajax
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_AdminAjax extends Jaws_Gadget_Action
{
    /**
     * Gets information of given theme
     *
     * @access   public
     * @internal param  string  $theme  Name of the theme
     * @return   array  Theme info
     */
    function GetThemeInfo()
    {
        @list($theme) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Themes');
        return $gadget->GetThemeInfo($theme);
    }
}