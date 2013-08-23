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
class Layout_Model_Layout extends Jaws_Gadget_Model
{
    /**
     * Get the layout items
     *
     * @access  public
     * @param   bool    $published  Publish status
     * @return  array   Returns an array with the layout items or Jaws_Error on failure
     */
    function GetLayoutItems($published = null)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $items = $lyTable->select(
            'id', 'gadget', 'gadget_action', 'action_params',
            'action_filename', 'display_when', 'section'
        );

        if (!is_null($published)) {
            $items->where('published', (bool)$published);
        }

        $lyTable->orderBy('layout_position asc');
        return $items->fetchAll();
    }

}