<?php
/**
 * Comments Gadget Admin
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the Comments gadget settings
     *
     * @access  public
     * @param   string  $allowComments  Allow comments?
     * @param   string  $allowDuplicate Allow duplicated comments?
     * @param   int     $orderType      Order type
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($allowComments, $allowDuplicate, $orderType)
    {
        $res = $this->gadget->registry->update('allow_comments', $allowComments);
        $res = $res && $this->gadget->registry->update('allow_duplicate', $allowDuplicate);
        $res = $res && $this->gadget->registry->update('order_type', $orderType);
        if ($res === false) {
            return Jaws_Error::raiseError(
                _t('COMMENTS_ERROR_CANT_UPDATE_PROPERTIES'),
                __FUNCTION__
            );
        }

        return true;
    }

}