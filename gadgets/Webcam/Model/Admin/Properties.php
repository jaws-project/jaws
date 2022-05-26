<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetModel
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Model_Admin_Properties extends Jaws_Gadget_Model
{
    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @param   int     $limit  The limitation
     * @return  mixed   True if change is successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($limit)
    {
        $res = $this->gadget->registry->update('limit_random', $limit);
        if ($res || !Jaws_Error::IsError($res)) {
            $this->gadget->session->push($this::t('PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }
        $this->gadget->session->push($this::t('ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error($this::t('ERROR_PROPERTIES_NOT_UPDATED'));
    }
}