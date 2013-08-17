<?php
require_once JAWS_PATH . 'gadgets/Layout/Model.php';
/**
 * Layout Core Gadget
 *
 * @category   GadgetModel
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_AdminModel extends Layout_Model
{
    /**
     * Get the layout sections
     *
     * @access  public
     * @return  array   Returns an array of layout mode sections and Jaws_Error on error
     */
    function GetLayoutSections()
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('section')->orderBy('section')->fetchRow();
    }

    /**
     * Add a new element to the layout
     *
     * @access  public
     * @param   string  $section         The section where it should appear
     * @param   string  $gadget          Gadget name
     * @param   string  $action          A ction name
     * @param   string  $action_params   Action's params
     * @param   string  $action_filename Filename that contant action method
     * @param   string  $pos             (Optional) Element position
     * @return  bool    Returns true if gadget was added without problems, if not, returns false
     */
    function NewElement($section, $gadget, $action, $action_params, $action_filename, $pos = '')
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        if (empty($pos)) {
            $pos = $lyTable->select('max(layout_position)')->where('section', $section)->fetchOne();
            if (Jaws_Error::IsError($pos)) {
                return false;
            }
            $pos += 1;
        }

        $lyTable->insert(array(
            'section'         => $section,
            'gadget'          => $gadget,
            'gadget_action'   => $action,
            'action_params'   => serialize($action_params),
            'action_filename' =>  empty($action_filename)? '' : $action_filename,
            'display_when'    => '*',
            'layout_position' => $pos,
            'published'       => true
        ));

        return $lyTable->exec();
    }

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
        $lyTable->where('gadget', $gadget)->and()->where('gadget_action', $gadget_action);
        return $lyTable->exec();
    }

    /**
     * Update the gadget action name
     *
     * @deprecated
     * @access  public
     * @param   string  $gadget      Gadget name
     * @param   string  $old_action  Old action
     * @param   string  $new_action  New action
     * @return  bool    Returns true if updated without problems, if not, returns false
     */
    function ChangeGadgetActionName($gadget, $old_action, $new_action)
    {
        return $this->EditGadgetLayoutAction($gadget, $old_action, $new_action);
    }

    /**
     * Delete an element
     *
     * @access  public
     * @param   int     $id         Element ID
     * @param   string  $section    Section name
     * @param   int     $position   Position of item in section
     * @return  bool    Returns true if element was removed otherwise it returns Jaws_Error
     */
    function DeleteElement($id, $section, $position)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        // begin transaction
        $lyTable->beginTransaction();
        $result = $lyTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_DELETED'));
            return $result;
        }

        $lyTable->update(array('layout_position'=>$lyTable->expr('layout_position - ?', 1)));
        $lyTable->where('section', $section)->and()->where('layout_position', (int)$position, '>=');
        $result = $lyTable->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        // commit transaction
        $lyTable->commit();
        return true;
    }

    /**
     * Move item
     *
     * @access  public
     * @param   int     $item           Item ID
     * @param   string  $old_section    Old section name
     * @param   int     $old_position   Position of item in old section
     * @param   string  $new_section    Old section name
     * @param   int     $new_position   Position of item in new section
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function MoveElement($item, $old_section, $old_position, $new_section, $new_position)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        // begin transaction
        $lyTable->beginTransaction();
        if ($old_section == $new_section) {
            if ($old_position > $new_position) {
                $lyTable->update(array('layout_position' => $lyTable->expr('layout_position + ?', 1)));
                $lyTable->where('section', $old_section)->and();
                $lyTable->where('layout_position', array($new_position, $old_position), 'between');
            } else {
                $lyTable->update(array('layout_position' => $lyTable->expr('layout_position - ?', 1)));
                $lyTable->where('section', $old_section)->and();
                $lyTable->where('layout_position', array($old_position, $new_position), 'between');
            }
        } else {
            $lyTable->update(array('layout_position' => $lyTable->expr('layout_position + ?', 1)));
            $lyTable->where('section', $new_section)->and();
            $lyTable->where('layout_position', $new_position, '>=');
            $result = $lyTable->exec();
            if (Jaws_Error::IsError($result)) {
                $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
                return $result;
            }

            $lyTable->update(array('layout_position' => $lyTable->expr('layout_position - ?', 1)));
            $lyTable->where('section', $old_section)->and();
            $lyTable->where('layout_position', $old_position, '>');
        }

        $result = $lyTable->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        $lyTable->update(array(
            'section' => $new_section,
            'layout_position' => $new_position
        ));
        $result = $lyTable->where('id', (int)$item)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        // commit transaction
        $GLOBALS['db']->dbc->commit();
        return true;
    }

    /**
     * Move a section to other place
     *
     * @access  public
     * @param   string  $from Which section to move
     * @param   string  $to   The destination
     * @return  bool    True if the section was moved without problems, if not it returns false
     */
    function MoveSection($from, $to)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $maxpos = $lyTable->select('max(layout_position)')->where('section', $to)->fetchOne();
        if (Jaws_Error::IsError($maxpos) || empty($maxpos)) {
            $maxpos = '0';
        }

        $lyTable->update(array(
            'section' => $to,
            'layout_position' => $lyTable->expr('layout_position + ?', $maxpos)
        ));
        return $result = $lyTable->where('section', $from)->exec();
    }


    /**
     * Get the properties of an element
     *
     * @access  public
     * @param   int     $id Element ID
     * @return  array   Returns an array with the properties of an element and false on error
     */
    function GetElement($id)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'gadget', 'gadget_action', 'action_params', 'action_filename',
            'display_when', 'layout_position', 'section', 'published'
        );
        return $lyTable->where('id', $id)->fetchRow();
    }

    /**
     * Get the gadgets that are in a section
     *
     * @access  public
     * @param   int     $id Section to search
     * @return  array   Returns an array of gadgets that are in a section and false on error
     */
    function GetGadgetsInSection($id)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select('id', 'gadget', 'gadget_action', 'display_when', 'layout_position', 'published');
        return $lyTable->where('section', $id)->orderBy('layout_position')->fetchAll();
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
     * Change when to display a gadget
     * 
     * @access  public
     * @param   int     $item   Item ID
     * @param   string  $dw     Display in these gadgets
     * @return  array   Response
     */
    function ChangeDisplayWhen($item, $dw) 
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->update(array('display_when' => $dw))->where('id', $item)->exec();
    }

    /**
     * Edit layout's element action
     * 
     * @access  public
     * @param   int     $item            Item ID
     * @params  string  $gadget_action   Action's name
     * @param   string  $action_params   Action's params
     * @param   string  $action_filename Filename that contant action method
     * @return  array   Response
     */
    function EditElementAction($item, $gadget_action, $action_params, $action_filename) 
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->update(array(
            'gadget_action'   => $gadget_action,
            'action_params'   => serialize($action_params),
            'action_filename' => (string)$action_filename
        ));
        return $lyTable->where('id', $item)->exec();
    }

    /**
     * Get layout actions of a given gadget
     * 
     * @access  public
     * @param   string  $gadget               Gadget's name
     * @param   bool    $associated_by_action Indexed by action's name
     * @return  array   Array with the actions of the given gadget
     */
    function GetGadgetLayoutActions($g, $associated_by_action = false)
    {
        $actions = $GLOBALS['app']->GetGadgetActions($g, 'layout', 'index');
        foreach ($actions as $key => $action) {
            if ($action['parametric']) {
                // set initial params
                $actions[$key]['parametric'] = false;
                $lParamsMethod = $key. 'LayoutParams';
                if (empty($action['file'])) {
                    // DEPRECATED: will be removed after all jaws official gadget converted
                    $objGadget = $GLOBALS['app']->LoadGadget($g, 'LayoutHTML');
                } else {
                    $objGadget = $GLOBALS['app']->LoadGadget($g, 'HTML', $action['file']);
                }

                if (!Jaws_Error::IsError($objGadget) && method_exists($objGadget, $lParamsMethod)) {
                    $actions[$key]['params'] = $objGadget->$lParamsMethod();
                }
            }

            $actions[$key] = array_merge(array('action' => $key), $actions[$key]);
        }

        return $associated_by_action? $actions : array_values($actions);
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