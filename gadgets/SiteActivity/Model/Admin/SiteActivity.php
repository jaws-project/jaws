<?php
/**
 * SiteActivity Model
 *
 * @category    GadgetModel
 * @package     SiteActivity
 */
class SiteActivity_Model_Admin_SiteActivity extends Jaws_Gadget_Model
{
    /**
     * Delete site activities
     *
     * @access  public
     * @param   array   $ids    Activity Ids
     * @return bool True or error
     */
    function DeleteSiteActivities($ids)
    {
        return Jaws_ORM::getInstance()->table('activities')->delete()->where('id', $ids, 'in')->exec();
    }

    /**
     * Delete all site activities
     *
     * @access  public
     * @return bool True or error
     */
    function DeleteAllSiteActivities()
    {
        return Jaws_ORM::getInstance()->table('activities')->delete()->exec();
    }
}