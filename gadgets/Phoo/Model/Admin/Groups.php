<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
     * Insert new group
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function AddGroup($insertData)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_group');
        return $table->insert($insertData)->exec();
    }

    /**
     * Update a group
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function EditGroup($gid, $updateData)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_group');
        return $table->update($updateData)->where('id', $gid)->exec();
    }
}