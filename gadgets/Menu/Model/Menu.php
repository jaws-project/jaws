<?php
/**
 * Menu Gadget
 *
 * @category    GadgetModel
 * @package     Menu
 */
class Menu_Model_Menu extends Jaws_Gadget_Model
{
    /**
     * Returns a menu
     *
     * @access  public
     * @param   int     $mid    menu ID
     * @return  mixed  Array with all the available menus and Jaws_Error on error
     */
    function GetMenu($mid)
    {
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $menusTable->select(
            'id:integer', 'pid:integer', 'gid:integer', 'gadget', 'title', 'url', 'variables', 'options',
            'permission', 'target:integer', 'order:integer', 'symbol', 'mega:boolean', 'status:integer', 'image:boolean'
        );
        return $menusTable->where('id', $mid)->fetchRow();
    }

    /**
     * Returns a list of  menus at a request level
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid    Group ID
     * @param   int     $status Menu status
     * @return  mixed   Array with all the available menus and Jaws_Error on error
     */
    function GetLevelsMenus($pid, $gid = null, $status = null)
    {
        // using boolean type for blob to check it empty or not
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $menusTable->select(
            'id:integer', 'gid:integer', 'gadget', 'title', 'url', 'variables', 'options', 'permission',
            'target:integer', 'symbol', 'mega:boolean', 'status:integer', 'image:boolean'
        );
        $menusTable->where('pid', $pid);

        if(!empty($gid)) {
            $menusTable->and()->where('gid', $gid);
        }

        if (!empty($status)) {
            $menusTable->and()->where('status', 0, '>');
        }

        return $menusTable->orderBy('gid asc', 'order asc')->fetchAll();
    }

    /**
     * Returns the image of the menu
     *
     * @access  public
     * @param   int     $id
     * @return  blob    image or Jaws_Error on error
     */
    function GetMenuImage($id)
    {
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $blob = $menusTable->select('image:blob')->where('id', (int)$id)->fetchOne();
        if (Jaws_Error::IsError($blob)) {
            return $blob;
        }

        $result = '';
        if (is_resource($blob)) {
            while (!feof($blob)) {
                $result.= fread($blob, 8192);
            }
        }
        return $result;
    }

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