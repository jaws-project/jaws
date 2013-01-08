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
            'rank:integer', 'visible:boolean', 'image');
        $result = $menusTable->where('id', $mid)->getRow();

        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('MENU_ERROR_GET_MENUS'), _t('MENU_NAME'));
        }

        return $result;
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
            'id:integer', 'gid:integer', 'title', 'url', 'url_target:integer', 'visible:integer', 'image:boolean');

        if(!empty($gid)) {
            $menusTable->where('gid', $gid)->and();
        }
        $result = $menusTable->where('pid', $pid)->and()->where('visible', 1)->orderBy('rank ASC')->getAll();

        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('MENU_ERROR_GET_MENUS'), _t('MENU_NAME'));
        }

        return $result;
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
            return new Jaws_Error($blob->getMessage(), 'SQL');
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
        $mgroupsTable->select('id:integer', 'title', 'title_view', 'view_type', 'rank:integer', 'visible:boolean');
        if(!empty($gid)) {
            $mgroupsTable->where('id', $gid);
        }
        $mgroupsTable->orderBy('rank DESC');

        if (!empty($gid)) {
            $result = $mgroupsTable->getRow();
        } else {
            $result = $mgroupsTable->getAll();
        }
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('MENU_ERROR_GET_GROUPS'), _t('MENU_NAME'));
        }

        return $result;
    }

}