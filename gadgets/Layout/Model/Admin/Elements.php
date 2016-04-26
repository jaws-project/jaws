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
     * @param   string  $layout     Layout name
     * @param   string  $title      Element title
     * @param   string  $section    The section where it should appear
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   string  $params     Action's params
     * @param   string  $filename   Filename that include action method
     * @param   string  $pos        (Optional) Element position
     * @return  bool    Returns true if gadget was added without problems, if not, returns false
     */
    function NewElement($layout, $title, $section,
        $gadget, $action, $params, $filename, $pos = ''
    ) {
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        if (empty($pos)) {
            $pos = $lyTable->select('max(position)')
                ->where('user', $user)
                ->and()
                ->where('theme', $this->gadget->theme)
                ->and()
                ->where('locality', $this->gadget->locality)
                ->and()
                ->where('layout', $layout)
                ->and()
                ->where('section', $section)
                ->fetchOne();
            if (Jaws_Error::IsError($pos)) {
                return false;
            }
            $pos += 1;
        }

        $lyTable->insert(array(
            'user'      => $user,
            'theme'     => $this->gadget->theme,
            'locality'  => $this->gadget->locality,
            'layout'    => $layout,
            'title'     => $title,
            'section'   => $section,
            'gadget'    => $gadget,
            'action'    => $action,
            'params'    => serialize($params),
            'filename'  =>  empty($filename)? '' : $filename,
            'when'      => '*',
            'position'  => $pos,
            'published' => true
        ));

        return $lyTable->exec();
    }

    /**
     * Delete an element
     *
     * @access  public
     * @param   int     $id         Element ID
     * @param   string  $layout     Layout name
     * @param   string  $section    Section name
     * @param   int     $position   Position of item in section
     * @return  bool    Returns true if element was removed otherwise it returns Jaws_Error
     */
    function DeleteElement($id, $layout, $section, $position)
    {
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        // begin transaction
        $lyTable->beginTransaction();
        $result = $lyTable->delete()->where('id', $id)->and()->where('user', $user)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_DELETED'));
            return $result;
        }

        $result = $lyTable->update(array('position'=>$lyTable->expr('position - ?', 1)))
            ->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
            ->and()
            ->where('layout', $layout)
            ->and()
            ->where('section', $section)
            ->and()
            ->where('position', (int)$position, '>=')
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
     * @param   string  $layout         Layout name
     * @param   string  $old_section    Old section name
     * @param   int     $old_position   Position of item in old section
     * @param   string  $new_section    Old section name
     * @param   int     $new_position   Position of item in new section
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function MoveElement($item, $layout, $old_section, $old_position, $new_section, $new_position)
    {
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        // begin transaction
        $lyTable->beginTransaction();
        if ($old_section == $new_section) {
            if ($old_position > $new_position) {
                $lyTable->update(array('position' => $lyTable->expr('position + ?', 1)));
                $lyTable->where('section', $old_section)->and();
                $lyTable->where('position', array($new_position, $old_position), 'between');
            } else {
                $lyTable->update(array('position' => $lyTable->expr('position - ?', 1)));
                $lyTable->where('section', $old_section)->and();
                $lyTable->where('position', array($old_position, $new_position), 'between');
            }
        } else {
            $lyTable->update(array('position' => $lyTable->expr('position + ?', 1)));
            $lyTable->where('user', $user)
                ->and()
                ->where('theme', $this->gadget->theme)
                ->and()
                ->where('locality', $this->gadget->locality)
                ->and()
                ->where('layout', $layout);
            $lyTable->and()->where('section', $new_section)->and();
            $lyTable->where('position', $new_position, '>=');
            $result = $lyTable->exec();
            if (Jaws_Error::IsError($result)) {
                $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
                return $result;
            }

            $lyTable->update(array('position' => $lyTable->expr('position - ?', 1)));
            $lyTable->where('section', $old_section)->and();
            $lyTable->where('position', $old_position, '>');
        }

        $result = $lyTable->and()->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
            ->and()
            ->where('layout', $layout)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        $lyTable->update(array(
            'section' => $new_section,
            'position' => $new_position
        ));
        $result = $lyTable->where('id', (int)$item)
            ->and()
            ->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
            ->and()
            ->where('layout', $layout)
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
     * @param   string  $layout Layout name
     * @param   string  $when   Display in these gadgets
     * @return  array   Response
     */
    function UpdateDisplayWhen($item, $layout, $when)
    {
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->update(array('when' => $when))
            ->where('id', $item)
            ->and()
            ->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
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
        if (Jaws_Error::IsError($actions)) {
            return array();
        }
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
     * @return  array   Returns an array with the properties of an element and false on error
     */
    function GetElement($id)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'gadget', 'action', 'params', 'filename',
            'when', 'position', 'section', 'published'
        );
        return $lyTable->where('id', $id)->fetchRow();
    }

}