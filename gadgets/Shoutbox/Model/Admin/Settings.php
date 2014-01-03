<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetModel
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Set the properties of the gadget
     *
     * @access  public
     * @param   int     $limit      Limit of shoutbox entries
     * @param   int     $max_strlen Maximum length of comment entry
     * @param   bool    $authority
     * @return  mixed   True if change was successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($limit, $max_strlen, $authority)
    {
        $res = $this->gadget->registry->update('limit', $limit);
        $res = $res && $this->gadget->registry->update('max_strlen', $max_strlen);
        $res = $res && $this->gadget->registry->update('anon_post_authority', ($authority == true)? 'true' : 'false');
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SHOUTBOX_ERROR_SETTINGS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SHOUTBOX_ERROR_SETTINGS_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('SHOUTBOX_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}