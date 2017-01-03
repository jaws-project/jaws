<?php
/**
 * Menu Gadget
 *
 * @category    GadgetModel
 * @package     Menu
 */
class Menu_Model_Group extends Jaws_Gadget_Model
{
    /**
     * Returns a list with all the menus
     *
     * @access  public
     * @param   int     $gid        group ID
     * @return  mixed  Array with all the available menus and Jaws_Error on error
     */
    function GetGroups($gid = null)
    {
        $mgroupsTable = Jaws_ORM::getInstance()->table('menus_groups');
        $mgroupsTable->select(
            'id:integer', 'title', 'title_view:integer', 'view_type:integer',
            'order:integer', 'published:boolean'
        );
        $mgroupsTable->orderBy('order desc');
        if(empty($gid)) {
            $result = $mgroupsTable->fetchAll();
        } else {
            $result = $mgroupsTable->where('id', $gid)->fetchRow();
        }

        return $result;
    }
}