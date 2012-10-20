<?php
/**
 * Layout Core Gadget
 *
 * @category   GadgetModel
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'gadgets/Layout/Model.php';

class LayoutAdminModel extends LayoutModel
{
    /**
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/Layout/pluggable', 'false');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.3.0', '<')) {
            $result = $this->installSchema('0.3.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.3.1', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Layout/ManageThemes',  'false');
        }

        if (version_compare($old, '0.4.0', '<')) {
            $result = $this->installSchema('0.4.0.xml', '', "0.3.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.5.0', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.4.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $items = $this->GetLayoutItems();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            $sql = '
                UPDATE [[layout]] SET
                    [gadget_action] = {gadget_action},
                    [action_params] = {action_params}
                WHERE [id] = {id}';

            foreach ($items as $item) {
                preg_match_all('/^([a-z0-9]+)\((.*?)\)$/i', $item['gadget_action'], $matches);
                if (isset($matches[1][0]) && isset($matches[2][0])) {
                    $item['gadget_action'] = $matches[1][0];
                    $item['action_params'] = array_filter(explode(',', $matches[2][0]));
                }
                $item['action_params'] = serialize($item['action_params']);
                $result = $GLOBALS['db']->query($sql, $item);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        }

        return true;
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
        if (empty($pos)) {
            $sql = '
            SELECT MAX([layout_position])
            FROM [[layout]]
            WHERE [section] = {section}';

            $pos = $GLOBALS['db']->queryOne($sql, array('section' => $section));
            if (Jaws_Error::IsError($pos)) {
                return false;
            }
            $pos += 1;
        }

        $params = array();
        $params['gadget']          = $gadget;
        $params['action']          = $action;
        $params['action_params']   = serialize($action_params);
        $params['action_filename'] = $action_filename;
        $params['displayWhen']     = '*';
        $params['section']         = $section;
        $params['pos']             = $pos;
        $sql = '
            INSERT INTO [[layout]]
                ([section], [gadget], [gadget_action], [action_params], [action_filename],
                 [display_when], [layout_position])
            VALUES
                ({section}, {gadget}, {action}, {action_params}, {action_filename},
                 {displayWhen}, {pos})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $lid = $GLOBALS['db']->lastInsertID('layout', 'id');
        return (Jaws_Error::IsError($lid))? false : $lid;
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
        $params = array();
        $params['gadget']          = $gadget;
        $params['old_action']      = $old_action;
        $params['gadget_action']   = $gadget_action;
        $params['action_filename'] = $action_filename;

        $sql = '
            UPDATE [[layout]] SET
                [gadget_action]   = {gadget_action},
                [action_filename] = {action_filename}
            WHERE
                [gadget] = {gadget}
              AND
                [gadget_action] = {old_action}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
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
     * @param   int     $id  Element ID
     * @return  bool    Returns true if element was removed, if not it returns false
     */
    function DeleteElement($id)
    {
        $element = $this->GetElement($id);
        if ($element === false) {
            return false;
        }

        $sql = 'DELETE FROM [[layout]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $res = $this->UpdateSectionPositions($element['section']);
        if ($res === false) {
            return false;
        }

        return true;
    }

     /**
     * Update the positions of a section
     *
     *  - If the position of an element doesn't match the sequence, a
     *    temp value will be used instead with the current and next values
     *  - If the position of an element is repeated, a temp value
     *    will be used with that element and the next elements
     *
     * @access  public
     * @param   int     $section       Section to move it
     * @param   int     $highpriority  Item with high priority
     * @return  bool    Success/Failure
     */
    function UpdateSectionPositions($section)
    {
        $sql = '
            SELECT
                [id], [layout_position]
            FROM [[layout]]
            WHERE [section] = {section}
            ORDER BY [layout_position]';
        $result = $GLOBALS['db']->queryAll($sql, array('section' => $section));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $elementsArray = array();
        $posCounter    = 1;
        $change        = false;
        $posUsed       = array();
        foreach ($result as $row) {
            $res = array();
            $res['id']       = $row['id'];
            $res['position'] = $row['layout_position'];
            if ($row['layout_position'] != $posCounter) {
                $change = true;
            }

            $res['new_position'] = ($change === true ? $posCounter : false);
            $posUsed[] = $posCounter;
            $elementsArray[$row['id']] = $res;
            $posCounter++;
        }

        foreach ($elementsArray as $element) {
            if ($element['new_position'] == false) {
                continue;
            }

            $params = array();
            $params['position'] = $element['new_position'];
            $params['section']  = $section;
            $params['id']       = $element['id'];

            $sql = 'UPDATE [[layout]] SET
                     [layout_position] = {position}
                    WHERE
                     [id] = {id}';
            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return false;
            }
        }
        return true;
    }

 

    /**
     * Move an element to a new section
     *
     * @access  public
     * @param   int     $id            Element ID
     * @param   int     $section       Section to move it
     * @param   int     $pos           Position that will be used, all other positions will be placed under this
     * @param   array   $sortedItems   An array with the sorted items of $section. WARNING: keys have the item_ prefix
     * @return  bool    Success/Failure
     */
    function MoveElementToSection($id, $section, $pos, $sortedItems)
    {
        $params = array();
        $params['id']      = $id;
        $params['section'] = $section;
        $params['pos']     = $pos;

        $element = $this->GetElement($id);
        if ($element === false) {
            return false;
        }

        $sql = '
            SELECT
             COUNT([id])
            FROM [[layout]]
            WHERE
             [section] = {section}';
        $count = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($count)) {
            return false;
        }
        $count = (int)$count;

        $sql = 'UPDATE [[layout]] SET
                 [layout_position] = {pos},
                 [section] = {section}
                 WHERE
                    [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        if ($count > 0) {
            $sortedItems = array_keys($sortedItems);
            $gadgets     = $this->GetGadgetsInSection($section);

            foreach ($gadgets as $gadget) {
                $newPos = array_search('item_'.$gadget['id'], $sortedItems);
                if ($newPos === false) {
                    continue;
                }

                $newPos = $newPos+1;
                if ($newPos == $gadget['layout_position']) {
                    continue;
                }

                $params        = array();
                $params['pos'] = $newPos;
                $params['id']  = $gadget['id'];


                $sql = 'UPDATE [[layout]] SET
                         [layout_position] = {pos}
                        WHERE
                         [id] = {id}';
                $result = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($result)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Move an element to another place
     *
     * @access  public
     * @param   int     $elementId Element ID
     * @param   string  $section   Section where it is(header, left, main, right, footer)
     * @param   string  $direction Where to move it
     */
    function MoveElement($elementId, $section, $direction)
    {
        ///FIXME:  Move up/down/left/right properly
        $sql = '
            SELECT
                [id], [layout_position]
            FROM [[layout]]
            WHERE [section] = {section}
            ORDER BY [layout_position]';
        ///FIXME check for errors
        $result = $GLOBALS['db']->queryAll($sql, array('section' => $section));

        $menu_array = array();
        foreach ($result as $row) {
            $res['id']              = $row['id'];
            $res['position']        = $row['layout_position'];
            $menu_array[$row['id']] = $res;
        }

        reset($menu_array);
        $found = false;
        while (!$found) {
            $v = current($menu_array);
            if ($v['id'] == $elementId) {
                $found = true;
                $position = $v['layout_position'];
                $id = $v['id'];
            } else {
                next($menu_array);
            }
        }

        $run_queries = false;
        if ($direction == 'up') {
            if (prev($menu_array)) {
                $v           = current($menu_array);
                $m_position  = $v['layout_position'];
                $m_id        = $v['id'];
                $run_queries = true;
            }
        } elseif ($direction == 'down') {
            if (next($menu_array))   {
                $v           = current($menu_array);
                $m_position  = $v['layout_position'];
                $m_id        = $v['id'];
                $run_queries = true;
            }
        }

        if ($run_queries) {
            $sql = '
                UPDATE [[layout]] SET
                    [layout_position] = {position}
                WHERE [id] = {id}';
            $GLOBALS['db']->query($sql, array('position' => $m_position, 'id' => $id));

            $GLOBALS['db']->query($sql, array('position' => $position, 'id' => $m_id));
        }
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
        $sql = 'SELECT MAX([layout_position]) FROM [[layout]] WHERE [section] = {to}';
        $maxpos = $GLOBALS['db']->queryOne($sql, array('to' => $to));
        if (Jaws_Error::IsError($maxpos) || empty($maxpos)) {
            $maxpos = '0';
        }

        $params           = array();
        $params['to']     = $to;
        $params['maxpos'] = $maxpos;
        $params['from']   = $from;
        $sql = '
            UPDATE [[layout]] SET
                [section] = {to},
                [layout_position] = [layout_position] + {maxpos}
            WHERE [section] = {from}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
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
        $sql = '
            SELECT
               [id], [gadget], [gadget_action], [action_params], [action_filename],
               [display_when], [layout_position], [section]
            FROM [[layout]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
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
        $sql = '
            SELECT
                [id], [gadget], [gadget_action], [display_when], [layout_position]
            FROM [[layout]]
            WHERE [section] = {section}
            ORDER BY [layout_position]';

        $result = $GLOBALS['db']->queryAll($sql, array('section' => $id));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
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
        $sql = 'DELETE FROM [[layout]] WHERE [gadget] = {gadget}';
        $result = $GLOBALS['db']->query($sql, array('gadget' => $gadget));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
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
        $params                = array();
        $params['id']          = $item;
        $params['displayWhen'] = $dw;
        $sql = '
            UPDATE [[layout]] SET
                [display_when] = {displayWhen}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        return true;
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
        $params = array();
        $params['id']     = $item;
        $params['gadget_action'] = $gadget_action;
        $params['action_params'] = serialize($action_params);
        $params['action_filename'] = $action_filename;

        $sql = '
            UPDATE [[layout]] SET
                [gadget_action]   = {gadget_action},
                [action_params]   = {action_params},
                [action_filename] = {action_filename}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }
        return true;
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
        $actions = array();
        $layoutGadget = $GLOBALS['app']->loadGadget($g, 'LayoutHTML');
        if (!Jaws_Error::IsError($layoutGadget)) {
            $actions = $GLOBALS['app']->GetGadgetActions($g, 'LayoutAction');
            foreach ($actions as $key => $action) {
                if ($action['params'] !== false) {
                    // set initial params
                    $actions[$key]['params'] = false;
                    $lParamsMethod = $key. 'LayoutParams';
                    $layoutHTML = $GLOBALS['app']->LoadGadget($g, 'LayoutHTML', $action['file']);
                    if (!Jaws_Error::IsError($layoutHTML) && method_exists($layoutHTML, $lParamsMethod)) {
                        $actions[$key]['params'] = $layoutHTML->$lParamsMethod();
                    }
                }

                $actions[$key] = array_merge(array('action' => $key), $actions[$key]);
                unset($actions[$key]['mode']);
            }

            // Deprecated since 0.8: This is for backwards compatibility
            if (method_exists($layoutGadget, 'LoadLayoutActions')) {
                $oldActions = $layoutGadget->LoadLayoutActions();
                $new_action = '';
                foreach ($oldActions as $action => $attributes) {
                    preg_match_all('/^([a-z0-9]+)\((.*?)\)$/i', $action, $matches);
                    if (isset($matches[1][0]) && isset($matches[2][0])) {
                        $action = $matches[1][0];
                        $param  = $matches[2][0];
                    }
                    if (isset($actions[$action])) {
                        if (isset($param)) {
                            $actions[$action]['params'][0]['value'] += array($param => $attributes['name']);
                            unset($param);
                        }
                    } else {
                        $actions[$action] = array(
                            'action' => $action,
                            'name'   => $action,
                            'desc'   => $attributes['desc'],
                            'params' => false,
                            'file'   => null
                        );

                        if (isset($param)) {
                            $actions[$action]['params'][0] = array(
                                'title' => '',
                                'value' => array($param => $attributes['name'])
                            );
                            unset($param);
                        }
                    }
                } // foreach
            } // if LoadLayoutActions exist
        }

        return $associated_by_action? $actions : array_values($actions);
    }

}