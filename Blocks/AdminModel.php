<?php
/**
 * Blocks Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Blocks/Model.php';

class BlocksAdminModel extends BlocksModel
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed    Returns True if installation success or Jaws_Error on any error found
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/Blocks/pluggable',  'false');

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  mixed   True on if successful or Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('blocks');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('BLOCKS_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blocks/pluggable');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    True on Success or Jaws_Error on Failure
     */
    function UpdateGadget($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys.
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Blocks/searchable');

        return true;
    }

    /**
     * Create a new Block
     *
     * @access  public
     * @param   string  $title          Block title
     * @param   string  $contents       Block contents
     * @param   bool    $display_title  True if we want to display block title
     * @param   int     $user           User ID
     * @return  mixed   Result array if successful or Jaws_Error or False on failure
     */
    function NewBlock($title, $contents, $display_title, $user)
    {
        $params = array();
        $params['user']  = $user;
        $params['title'] = $title;
        $params['contents'] = $contents;

        $params['now']      = $GLOBALS['db']->Date();
        $params['display_title'] = $display_title ? true : false;

        $sql = '
            INSERT INTO [[blocks]]
                ([title], [contents], [display_title],
                [created_by], [createtime],
                [modified_by], [updatetime])
            VALUES
                ({title}, {contents}, {display_title},
                {user}, {now},
                {user}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOCKS_ERROR_NOT_ADDED'), _t('BLOCKS_NAME'));
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ADDED', $title), RESPONSE_NOTICE);

        ///NOTE this might be obselete by lastInsertID.
        $sql = 'SELECT [id] FROM [[blocks]] WHERE [createtime] = {now}';
        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row['id'];
        }

        return false;
    }

    /**
     * Update Block
     *
     * @access  public
     * @param   int     $id             Block ID
     * @param   string  $title          Block title
     * @param   string  $contents       Block contents
     * @param   bool    $display_title  True if we want to display block title
     * @param   int     $user           User ID
     * @return  mixed   True if query is successful, if not, returns Jaws_Error on any error
     */
    function UpdateBlock($id, $title, $contents, $display_title, $user)
    {
        $params = array();
        $params['id']    = (int)$id;
        $params['user']  = $user;
        $params['title'] = $title;
        $params['contents'] = $contents;
        $params['now']      = $GLOBALS['db']->Date();
        $params['display_title'] = ($display_title ? true : false);

        $sql = '
            UPDATE [[blocks]] SET
                [title] = {title},
                [contents] = {contents},
                [display_title] = {display_title},
                [modified_by] = {user},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOCKS_ERROR_NOT_UPDATED'), _t('BLOCKS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_UPDATED', $title), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a block
     *
     * @access  public
     * @param   int     $id     Block ID
     * @return  mixed   True if query is successful, if not, returns Jaws_Error on any error
     */
    function DeleteBlock($id)
    {
        $b = $this->GetBlock($id);
        $params = array();
        $params['id'] = $id;
        $sql = "DELETE FROM [[blocks]] WHERE [id] = {id}";
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOCKS_ERROR_NOT_UPDATED'), _t('BLOCKS_NAME'));
        }
        // Remove from layout
        $params['action'] = "Display({$id})";
        $sql = "DELETE FROM [[layout]] WHERE [gadget] = 'Blocks' AND [gadget_action] = {action}";
        $result = $GLOBALS['db']->query($sql, $params);

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_DELETED', $b['title']), RESPONSE_NOTICE);
        return true;
    }
}