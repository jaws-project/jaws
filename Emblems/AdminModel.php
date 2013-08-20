<?php
/**
 * Emblems Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_AdminModel extends Jaws_Gadget_Model
{
    /**
     * Updates the gadget properties in the registry
     *
     * @access  public
     * @param   int      $rows        Number of rows that will display the gadget
     * @param   bool     $allow_url   If the emblems will display the link or not
     * @return  mixed    True if properties got updated, Jaws_Error otherwise
     */
    function UpdateProperties($rows, $allow_url)
    {
        $result = $this->gadget->registry->update('rows', $rows);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), _t('EMBLEMS_NAME'));
        }
        $result = $this->gadget->registry->update('allow_url', $allow_url);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'),
                                 _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}