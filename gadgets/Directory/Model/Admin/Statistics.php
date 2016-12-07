<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 */
class Directory_Model_Admin_Statistics extends Jaws_Gadget_Model
{
    /**
     * Fetches statistics
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  mixed   Query result
     */
    function GetStatistics($user = null)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $result = array();

        // Total
        $table->select('count(id):integer')->where('public', true);
        $result['total'] = $table->fetchOne();

        // Directories
        $table->reset();
        $table->select('count(id):integer')->where('public', true);
        $table->where('is_dir', true);
        $result['dirs'] = $table->fetchOne();

        // Files
        $result['files'] = $result['total'] - $result['dirs'];

        return $result;
    }
}