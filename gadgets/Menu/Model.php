<?php
/**
 * Menu Gadget
 *
 * @category   GadgetModel
 * @package    Menu
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Menu_Model extends Jaws_Gadget_Model
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
            'id:integer', 'pid:integer', 'gid:integer', 'menu_type', 'title', 'url', 'url_target:integer',
            'rank:integer', 'visible:integer', 'image:boolean');
        return $menusTable->where('id', $mid)->getRow();
    }

    /**
     * Returns a list of  menus at a request level
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid            group ID
     * @param   bool    $onlyVisible    show only visible
     * @return  mixed   Array with all the available menus and Jaws_Error on error
     */
    function GetLevelsMenus($pid, $gid = null, $onlyVisible = false)
    {
        // using boolean type for blob to check it empty or not
        $menusTable = Jaws_ORM::getInstance()->table('menus');
        $menusTable->select(
            'id:integer', 'gid:integer', 'title', 'url', 'url_target:integer',
            'visible:integer', 'image:boolean'
        );
        $menusTable->where('pid', $pid);

        if(!empty($gid)) {
            $menusTable->and()->where('gid', $gid);
        }

        if($onlyVisible) {
            $menusTable->and()->where('visible', 1);
        }

        return $menusTable->orderBy('rank ASC')->getAll();
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
        $blob = $menusTable->select('image:blob')->where('id', (int)$id)->getOne();
        if (Jaws_Error::IsError($blob)) {
            return $blob;
        }

        $result = '';
        while (!feof($blob)) {
            $result.= fread($blob, 8192);
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
            'rank:integer', 'visible:integer'
        );
        $mgroupsTable->orderBy('rank DESC');
        if(empty($gid)) {
            $result = $mgroupsTable->getAll();
        } else {
            $result = $mgroupsTable->where('id', $gid)->getRow();
        }

        return $result;
    }

}