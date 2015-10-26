<?php
/**
 * Layout Core Gadget
 *
 * @category   GadgetModel
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model_Admin_Layout extends Jaws_Gadget_Model
{
    /**
     * Update the gadget layout action name/file
     *
     * @access  public
     * @param   string  $gadget             Gadget name
     * @param   string  $old_action         Old action name
     * @param   string  $gadget_action      New action name
     * @param   string  $action_filename    New action file
     * @return  bool    Returns true if updated without problems, otherwise returns false
     */
    function EditGadgetLayoutAction($gadget, $old_action, $gadget_action, $action_filename = '')
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->update(array(
            'gadget_action'   => $gadget_action,
            'action_filename' => $action_filename
        ));
        $lyTable->where('gadget', $gadget)->and()->where('gadget_action', $old_action);
        return $lyTable->exec();
    }

    /**
     * Get the gadgets that are in a section
     *
     * @access  public
     * @param   bool    $index      Elements in index layout
     * @param   string  $section    Section to search
     * @return  array   Returns an array of gadgets that are in a section and false on error
     */
    function GetGadgetsInSection($index, $section, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select('id', 'gadget', 'gadget_action', 'display_when', 'layout_position', 'published')
            ->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->and()
            ->where('section', $section);
        return $lyTable->orderBy('layout_position')->fetchAll();
    }

    /**
     * Delete all the elements of a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget's name
     * @return  bool    Returns true if element was removed, if not it returns false
     */
    function DeleteGadgetElements($gadget)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->delete()->where('gadget', $gadget)->exec();
    }


    /**
     * Edit layout's element action
     *
     * @access  public
     * @param   int     $item            Item ID
     * @params  string  $gadget_action   Action's name
     * @param   string  $action_params   Action's params
     * @param   string  $action_filename Filename that contant action method
     * @param   int     $user            (Optional) User's ID
     * @return  array   Response
     */
    function UpdateElementAction($item, $gadget_action, $action_params, $action_filename, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->update(array(
            'gadget_action'   => $gadget_action,
            'action_params'   => serialize($action_params),
            'action_filename' => (string)$action_filename
        ));
        return $lyTable->where('id', $item)->and()->where('user', (int)$user)->exec();
    }

    /**
     * Update publish status of all elements related the gadget
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   bool    $published  Publish status
     * @return  bool    True if query was successful and Jaws_Error on error
     */
    function PublishGadgetElements($gadget, $published)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $res = $lyTable->update(array('published'=>(bool)$published))->where('gadget', $gadget)->exec();
        return $res;
    }

}