<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Model_Statistics extends Jaws_Gadget_Model
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
        $table->select('count(id):integer');
        if ($user) {
            $table->where('user', $user);
            $table->and()->where('owner', $user);
        }
        $result['total'] = $table->fetchOne();

        // Directories
        $table->reset();
        $table->select('count(id):integer');
        $table->where('is_dir', true);
        if ($user) {
            $table->and()->where('user', $user);
            $table->and()->where('owner', $user);
        }
        $result['dirs'] = $table->fetchOne();

        // Files
        $result['files'] = $result['total'] - $result['dirs'];

        // Shared
        $table->select('count(id):integer');
        $table->where('shared', true);
        if ($user) {
            $table->and()->where('user', $user);
            $table->and()->where('owner', $user);
        }
        $result['shared'] = $table->fetchOne();

        // Foreign
        $table->select('count(id):integer');
        if ($user) {
            $table->and()->where('user', $user, '<>');
            $table->and()->where('owner', $user);
        }
        $result['foreign'] = $table->fetchOne();

        // Public
        $table->select('count(id):integer');
        $table->where('public', true);
        if ($user) {
            $table->and()->where('user', $user);
            $table->and()->where('owner', $user);
        }
        $result['public'] = $table->fetchOne();

        return $result;
    }
}