<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates gadget settings
     *
     * @access  public
     * @param   string  $defaultPage  Default page to be displayed
     * @param   string  $multiLang    Whether uses a multilanguage 'schema'?
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateSettings($defaultPage, $multiLang)
    {
        $res = array();
        $res[0] = $this->gadget->registry->update('default_page', $defaultPage);
        $res[1] = $this->gadget->registry->update('multilanguage', $multiLang);

        foreach($res as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('STATICPAGE_ERROR_SETTINGS_NOT_SAVED'));
            }
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('STATICPAGE_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }


}