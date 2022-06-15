<?php
/**
 * ServerTime Gadget
 *
 * @category   GadgetModel
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTime_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Updates the properties of ServerTime
     *
     * @access  public
     * @param   string  $format    The format of date and time being displayed
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateProperties($format)
    {
        $res = $this->gadget->registry->update('date_format', $format);
        if ($res) {
            $this->gadget->session->push($this::t('PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }

        $this->gadget->session->push($this::t('ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error($this::t('ERROR_PROPERTIES_NOT_UPDATED'));
    }

}