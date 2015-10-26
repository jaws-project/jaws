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
class Layout_Model_Admin_Elements extends Jaws_Gadget_Model
{
    /**
     * Add a new element to the layout
     *
     * @access  public
     * @param   bool    $index           Index layout
     * @param   string  $section         The section where it should appear
     * @param   string  $gadget          Gadget name
     * @param   string  $action          A ction name
     * @param   string  $action_params   Action's params
     * @param   string  $action_filename Filename that contant action method
     * @param   string  $pos             (Optional) Element position
     * @param   int     $user            (Optional) User's ID
     * @return  bool    Returns true if gadget was added without problems, if not, returns false
     */
    function NewElement($index, $section, $gadget, $action, $action_params, $action_filename, $pos = '', $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        if (empty($pos)) {
            $pos = $lyTable->select('max(layout_position)')
                ->where('user', $user)
                ->and()
                ->where('index', (bool)$index)
                ->and()
                ->where('section', $section)
                ->fetchOne();
            if (Jaws_Error::IsError($pos)) {
                return false;
            }
            $pos += 1;
        }

        $lyTable->insert(array(
            'user'            => $user,
            'index'           => (bool)$index,
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
     * Delete an element
     *
     * @access  public
     * @param   int     $id         Element ID
     * @param   bool    $index      Index layout
     * @param   string  $section    Section name
     * @param   int     $position   Position of item in section
     * @param   int     $user       (Optional) User's ID
     * @return  bool    Returns true if element was removed otherwise it returns Jaws_Error
     */
    function DeleteElement($id, $index, $section, $position, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        // begin transaction
        $lyTable->beginTransaction();
        $result = $lyTable->delete()->where('id', $id)->and()->where('user', (int)$user)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_DELETED'));
            return $result;
        }

        $result = $lyTable->update(array('layout_position'=>$lyTable->expr('layout_position - ?', 1)))
            ->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->and()
            ->where('section', $section)
            ->and()
            ->where('layout_position', (int)$position, '>=')
            ->exec();
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
     * @param   bool    $index          Index layout
     * @param   string  $old_section    Old section name
     * @param   int     $old_position   Position of item in old section
     * @param   string  $new_section    Old section name
     * @param   int     $new_position   Position of item in new section
     * @param   int     $user           (Optional) User's ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function MoveElement($item, $index, $old_section, $old_position, $new_section, $new_position, $user = 0)
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
            $lyTable->where('user', (int)$user)->and()->where('index', (bool)$index);
            $lyTable->and()->where('section', $new_section)->and();
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

        $result = $lyTable->and()->where('user', (int)$user)->and()->where('index', (bool)$index)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        $lyTable->update(array(
            'section' => $new_section,
            'layout_position' => $new_position
        ));
        $result = $lyTable->where('id', (int)$item)
            ->and()
            ->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        // commit transaction
        Jaws_DB::getInstance()->dbc->commit();
        return true;
    }

    /**
     * Update when to display a gadget
     *
     * @access  public
     * @param   int     $item   Item ID
     * @param   string  $dw     Display in these gadgets
     * @param   int     $user   (Optional) User's ID
     * @return  array   Response
     */
    function UpdateDisplayWhen($item, $dw, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->update(array('display_when' => $dw))
            ->where('id', $item)
            ->and()
            ->where('user', (int)$user)
            ->exec();
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
        $actions = Jaws_Gadget::getInstance($g)->action->fetchAll('index');
        foreach ($actions as $key => $action) {
            if (!isset($action['layout']) || empty($action['layout'])) {
                unset($actions[$key]);
                continue;
            }

            $actions[$key]['action'] = $key;
            $actions[$key]['name'] = _t(strtoupper($g.'_ACTIONS_'.$key));
            $actions[$key]['desc'] = _t(strtoupper($g.'_ACTIONS_'.$key.'_DESC'));
            if (isset($action['parametric']) && $action['parametric']) {
                // set initial params
                $actions[$key]['parametric'] = false;
                $lParamsMethod = $key. 'LayoutParams';
                $objGadget = Jaws_Gadget::getInstance($g)->action->load($action['file']);
                if (!Jaws_Error::IsError($objGadget) && method_exists($objGadget, $lParamsMethod)) {
                    $actions[$key]['params'] = $objGadget->$lParamsMethod();
                }
            }

            $actions[$key] = array_merge(array('action' => $key), $actions[$key]);
        }

        return $associated_by_action? $actions : array_values($actions);
    }

    /**
     * Get the properties of an element
     *
     * @access  public
     * @param   int     $id     Element ID
     * @param   int     $user   (Optional) User's ID
     * @return  array   Returns an array with the properties of an element and false on error
     */
    function GetElement($id, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'gadget', 'gadget_action', 'action_params', 'action_filename',
            'display_when', 'layout_position', 'section', 'published'
        );
        return $lyTable->where('id', $id)->and()->where('user', (int)$user)->fetchRow();
    }

}