<?php
/**
 * Blocks Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Model_Admin_Block extends Jaws_Gadget_Model
{
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
        $data = array();
        $data['title'] = $title;
        $data['contents'] = $contents;
        $data['display_title'] = $display_title ? true : false;
        $data['created_by'] = $data['modified_by'] = $user;
        $data['createtime'] = $data['updatetime'] = $GLOBALS['db']->Date();

        $blocksTable = Jaws_ORM::getInstance()->table('blocks');
        $result = $blocksTable->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->SetMessage(_t('BLOCKS_ERROR_NOT_ADDED'));
            return $result;
        }

        return $GLOBALS['db']->lastInsertID('blocks', 'id');
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
        $data = array();
        $data['title'] = $title;
        $data['contents'] = $contents;
        $data['display_title'] = ($display_title ? true : false);
        $data['modified_by'] = $user;
        $data['updatetime'] = $GLOBALS['db']->Date();

        $blocksTable = Jaws_ORM::getInstance()->table('blocks');
        $result = $blocksTable->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOCKS_ERROR_NOT_UPDATED'));
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
        $model = $this->gadget->model->load('Block');
        $block = $model->GetBlock($id);
        $blocksTable = Jaws_ORM::getInstance()->table('blocks');
        $result = $blocksTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('BLOCKS_ERROR_NOT_UPDATED'));
        }
        // TODO: we must trigger SHOUT here

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOCKS_DELETED', $block['title']), RESPONSE_NOTICE);
        return true;
    }

}