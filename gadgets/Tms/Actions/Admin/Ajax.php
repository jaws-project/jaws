<?php
/**
 * TMS (Theme Management System) AJAX API
 *
 * @category   Ajax
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_Actions_Admin_Ajax extends Jaws_Gadget_Action
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

    /**
     * Delete a theme
     *
     * @access   public
     * @internal param  string  $theme  Name of the theme
     * @return   array  Response array (notice or error)
     */
    function DeleteTheme()
    {
        @list($theme) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Themes');
        $res = $gadget->DeleteTheme($theme);
        if (Jaws_Error::IsError($res) || !$res) {
            $GLOBALS['app']->Session->PushLastResponse(_t('TMS_ERROR_CANT_DELETE_THEME'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('TMS_RESPONSE_THEME_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}