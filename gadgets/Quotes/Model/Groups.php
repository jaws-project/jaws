<?php
/**
 * Quotes Gadget
 *
 * @category   GadgetModel
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Model_Groups extends Jaws_Gadget_Model
{

    /**
     * Gets data of the group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  array   Group data array or Jaws_Error
     */
    function GetGroup($gid)
    {
        $qgTable = Jaws_ORM::getInstance()->table('quotes_groups');
        $res = $qgTable->select(
            'id:integer', 'title', 'view_mode:integer', 'view_type:integer', 'show_title:boolean',
            'limit_count:integer', 'random:boolean', 'published:boolean'
        );
        return $qgTable->where('id', $gid)->fetchRow();
    }

    /**
     * Retrieves groups
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @param   int     $id     Quote ID
     * @return  array   List of available groups or Jaws_Error
     */
    function GetGroups($gid = -1, $id = -1)
    {
        $qgTable = Jaws_ORM::getInstance()->table('quotes_groups');
        $qgTable->select(
            'id:integer', 'title', 'view_mode:integer', 'view_type:integer', 'show_title:boolean',
            'limit_count:integer', 'random:boolean', 'published:boolean'
        );

        if (($gid != -1) && ($id != -1)) {
            $qgTable->join('quotes', 'quotes_groups.gid', 'quotes.id');
            $qgTable->where('quotes_groups.id', $gid)->and()->where('quotes.id', $id)->orderBy('quotes_groups.id asc');
        } elseif ($id != -1) {
            $qgTable->join('quotes', 'quotes_groups.gid', 'quotes.id');
            $qgTable->where('quotes.id', $id)->orderBy('quotes_groups.id asc');
        } elseif ($gid != -1) {
            $qgTable->where('id', $gid)->orderBy('id asc');
        } else {
            $qgTable->orderBy('id asc');
        }

        $res = $qgTable->fetchAll();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage());
        }

        return $res;
    }


}