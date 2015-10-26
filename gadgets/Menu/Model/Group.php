<?php
/**
 * Menu Gadget
 *
 * @category     GadgetModel
 * @package     Menu
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
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
            'rank:integer', 'published:boolean'
        );
        $mgroupsTable->orderBy('rank desc');
        if(empty($gid)) {
            $result = $mgroupsTable->fetchAll();
        } else {
            $result = $mgroupsTable->where('id', $gid)->fetchRow();
        }

        return $result;
    }
}