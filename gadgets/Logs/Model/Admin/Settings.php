<?php
/**
 * Logs Gadget Admin
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the Tag gadget settings
     *
     * @access  public
     * @param   array   $settings  Setting data
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($settings)
    {
        $result = array();
        $result[] = $this->gadget->registry->update('log_priority_level', $settings['log_priority_level']);
        $result[] = $this->gadget->registry->update('log_parameters', $settings['log_parameters']);

        foreach ($result as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LOGS_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('LOGS_ERROR_SETTINGS_NOT_SAVE'));
            }
        }
        return true;
    }

}