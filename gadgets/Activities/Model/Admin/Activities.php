<?php
/**
 * Activities Model
 *
 * @category    GadgetModel
 * @package     Activities
 */
class Activities_Model_Admin_Activities extends Jaws_Gadget_Model
{
    /**
     * Delete activities
     *
     * @access  public
     * @param   array   $ids    Activity Ids
     * @return bool True or error
     */
    function DeleteActivities($ids)
    {
        return Jaws_ORM::getInstance()->table('activities')->delete()->where('id', $ids, 'in')->exec();
    }

    /**
     * Delete all activities
     *
     * @access  public
     * @return bool True or error
     */
    function DeleteAllActivities()
    {
        return Jaws_ORM::getInstance()->table('activities')->delete()->exec();
    }
}