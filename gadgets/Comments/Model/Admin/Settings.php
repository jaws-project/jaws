<?php
/**
 * Comments Gadget Admin
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the Comments gadget settings
     *
     * @access  public
     * @param   string  $allowComments  Allow comments?
     * @param   int     $defaultStatus  Default comment status
     * @param   int     $orderType      Order type
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($allowComments, $defaultStatus, $orderType)
    {
        $res = $this->gadget->registry->update('allow_comments', $allowComments);
        $res = $res && $this->gadget->registry->update('default_comment_status', $defaultStatus);
        $res = $res && $this->gadget->registry->update('order_type', $orderType);
        if ($res === false) {
            return Jaws_Error::raiseError(
                $this::t('ERROR_CANT_UPDATE_PROPERTIES'),
                __FUNCTION__
            );
        }

        return true;
    }

}