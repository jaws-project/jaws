<?php
/**
 * Settings Admin Gadget
 *
 * @category    GadgetAdmin
 * @package     Settings
 */
class Settings_Actions_Cache extends Jaws_Gadget_Action
{
    /**
     * deletes expired cache
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function CleanupExpiredCache()
    {
        return $this->app->cache->deleteExpiredKeys();
    }

}