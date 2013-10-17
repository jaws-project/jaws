<?php
/**
 * Banner Gadget
 *
 * @category   GadgetModel
 * @package    Banner
 */
class Banner_Model_Groups extends Jaws_Gadget_Model
{

    /**
     * Retrieve group's info
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   An array of group's data or Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        $bgroupsTable->select(
            'id:integer', 'title', 'limit_count:integer', 'show_title:boolean', 'show_type:integer', 'published:boolean'
        );

        return $bgroupsTable->where('id', $gid)->fetchRow();
    }

    /**
     * Retrieve groups
     *
     * @access  public
     * @param   int     $gid    group ID
     * @param   int     $bid    banner ID
     * @param   int     $columns
     * @return  mixed   An array of available banners or Jaws_Error on error
     */
    function GetGroups($gid = -1, $bid = -1, $columns = null)
    {
        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        if (empty($columns)) {
            $columns = array('id:integer', 'title', 'limit_count:integer', 'published:boolean');
        }

        $bgroupsTable->select($columns);

        if (($gid != -1) && ($bid != -1)) {
            $bgroupsTable->join('banners', 'banners.gid', 'banners_groups.id');
            $bgroupsTable->where('banners_groups.id', $gid)->and()->where('banners.id', $bid);
            $bgroupsTable->orderBy('banners_groups.id asc');
        } elseif ($bid != -1) {
            $bgroupsTable->join('banners', 'banners.gid', 'banners_groups.id');
            $bgroupsTable->where('banners.id', $bid);
            $bgroupsTable->orderBy('banners_groups.id asc');
        } elseif ($gid != -1) {
            $bgroupsTable->where('id', $gid);
        } else {
            $bgroupsTable->orderBy('id asc');
        }

        return $bgroupsTable->fetchAll();
    }


}