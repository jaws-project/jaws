<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Updates settings
     *
     * @access  public
     * @param   bool    $enabled        Should maps be used?
     * @param   bool    $use_aliases    Should aliases be used?
     * @param   bool    $precedence     custom map precedence over default map
     * @param   string  $extension      Extension to use
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function SaveSettings($enabled, $use_aliases, $precedence, $extension)
    {
        $res = $this->gadget->registry->update('map_enabled', ($enabled === true)? 'true' : 'false');
        $res = $res && $this->gadget->registry->update('map_custom_precedence', ($precedence === true)?  'true' : 'false');
        $res = $res && $this->gadget->registry->update('map_extensions',  $extension);
        $res = $res && $this->gadget->registry->update('map_use_aliases', ($use_aliases === true)? 'true' : 'false');

        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_SETTINGS_NOT_SAVED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }
}