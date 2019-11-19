<?php
/**
 * Files Gadget Admin
 *
 * @category    GadgetModel
 * @package     Files
 */
class Files_Model_Admin_Files extends Jaws_Gadget_Model
{
    /**
     * Delete all gadget tags
     *
     * @access  public
     * @param   string  $gadget     gadget name
     * @return  mixed   True or Jaws_Error on failure
     */
    function deleteGadgetFiles($gadget)
    {
        return Jaws_ORM::getInstance()->table('files')->delete()->where('gadget', $gadget)->exec();
    }

}