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

        // registry keys
        $this->AddRegistry('pluggable', 'false');
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
     * @param   int     $id         Element ID
     * @param   string  $section    Section name
     * @param   int     $position   Position of item in section
     * @return  bool    Returns true if element was removed otherwise it returns Jaws_Error
     */
    function DeleteElement($id, $section, $position)
    {
        $params = array();
        $params['id'] = (int)$id;

        $sql = '
            DELETE
                FROM [[layout]]
            WHERE
                [id] = {id}';

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_DELETED'));
            return $result;
        }

        $params['section'] = $section;
        $params['pos']     = (int)$position;
        $params['one']     = 1;

        $sql = '
            UPDATE [[layout]] SET
                [layout_position] = [layout_position] - {one}
            WHERE
                [section] = {section}
              AND
                [layout_position] >= {pos}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();
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
        $params = array();
        $params['section'] = $new_section;
        $params['one']     = 1;
        $params['pos']     = (int)$new_position;

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $sql = '
            UPDATE [[layout]] SET
                [layout_position] = [layout_position] + {one}
            WHERE
                [section] = {section}
              AND
                [layout_position] >= {pos}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        $params['id'] = (int)$item;
        $sql = '
            UPDATE [[layout]] SET
                [section] = {section},
                [layout_position] = {pos}
            WHERE
                [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        $params['section'] = $old_section;
        $params['pos']     = (int)$old_position;

        $sql = '
            UPDATE [[layout]] SET
                [layout_position] = [layout_position] - {one}
            WHERE
                [section] = {section}
              AND
                [layout_position] >= {pos}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('LAYOUT_ERROR_ELEMENT_MOVED'));
            return $result;
        }

        //Commit Transaction
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

}