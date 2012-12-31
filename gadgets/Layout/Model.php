<?php
/**
 * Model class (has the heavy queries) to manage layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model extends Jaws_Gadget_Model
{
    /**
     * Get the layout sections
     *
     * @access  public
     * @return  array   Returns an array of layout mode sections and Jaws_Error on error
     */
    function GetLayoutSections()
    {
        $sql = 'SELECT [section]
                FROM [[layout]]
                ORDER BY [section]';

        $res = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Get the layout items
     *
     * @access  public
     * @return  array   Returns an array with the layout items and false on error
     */
    function GetLayoutItems()
    {
        $sql = '
            SELECT
                [id],
                [gadget],
                [gadget_action],
                [action_params],
                [action_filename],
                [display_when],
                [section]
            FROM [[layout]] 
            ORDER BY [section], [layout_position]';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

}