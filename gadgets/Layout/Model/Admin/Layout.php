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
     * @param   string  $gadget     Gadget name
     * @param   string  $old_action Old action name
     * @param   string  $action     New action name
     * @param   string  $filename   New action file
     * @return  bool    Returns true if updated without problems, otherwise returns false
     */
    function EditGadgetLayoutAction($gadget, $old_action, $action, $filename = '')
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->update(array(
            'action'   => $action,
            'filename' => $filename
        ));
        $lyTable->where('gadget', $gadget)->and()->where('action', $old_action);
        return $lyTable->exec();
    }

    /**
     * Get the gadgets that are in a section
     *
     * @access  public
     * @param   string  $layout     Layout name
     * @param   string  $section    Section to search
     * @return  array   Returns an array of gadgets that are in a section and false on error
     */
    function GetGadgetsInSection($layout, $section)
    {
        $user = 0;
        if ($layout == 'Index.Dashboard') {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select('id', 'gadget', 'action', 'when', 'position', 'published')
            ->where('user', (int)$user)
            ->and()
            ->where('layout', $layout)
            ->and()
            ->where('section', $section);
        return $lyTable->orderBy('position')->fetchAll();
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
     * @param   int     $item       Item ID
     * @param   string  $layout     Layout name
     * @params  string  $action     Action's name
     * @param   string  $params     Action's params
     * @param   string  $filename   Filename that include action method
     * @return  array   Response
     */
    function UpdateElementAction($item, $layout, $action, $params, $filename)
    {
        $user = 0;
        if ($layout == 'Index.Dashboard') {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->update(array(
            'action'   => $action,
            'params'   => serialize($params),
            'filename' => (string)$filename
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