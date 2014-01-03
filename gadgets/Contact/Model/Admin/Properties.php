<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Set properties of the gadget
     *
     * @access  public
     * @param   bool    $use_antispam
     * @param   string  $email_format
     * @param   bool    $enable_attachment
     * @param   bool    $comments
     * @return  mixed   True if change is successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($use_antispam, $email_format, $enable_attachment, $comments)
    {
        $rs = array();
        $rs[] = $this->gadget->registry->update('use_antispam',      $use_antispam);
        $rs[] = $this->gadget->registry->update('email_format',      $email_format);
        $rs[] = $this->gadget->registry->update('enable_attachment', $enable_attachment);
        $rs[] = $this->gadget->registry->update('comments',          $comments);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || !$r) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('CONTACT_ERROR_PROPERTIES_NOT_UPDATED'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}
