<?php
/**
 * Domains Model
 *
 * @category    GadgetModel
 * @package     Users
 */
class Users_Model_Domains extends Jaws_Gadget_Model
{
    /**
     * Fetches domains
     *
     * @access  public
     * @return  mixed   returns domain data array otherwise Jaws_Error on failure
     */
    function getDomains()
    {
        return Jaws_ORM::getInstance()
            ->table('domains')
            ->select('id:integer', 'title')
            ->orderBy('insert_time desc')
            ->fetchAll();
    }

}