<?php
/**
 * BLOCKS AJAX API
 *
 * @category   Ajax
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Get Block
     *
     * @access  public
     * @param   int      $id     Block ID
     * @return  mixed    Block data or False on error
     */
    function GetBlock($id)
    {
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'Model', 'Block');
        $block = $model->GetBlock($id);
        if (Jaws_Error::IsError($block)) {
            return false;
        }

        return $block;
    }

    /**
     * Create a new  block
     *
     * @access  public
     * @param   string  $title          Block title
     * @param   string  $contents       Block contents
     * @param   bool    $displayTitle   If true display block title
     * @return  array   Response array (notice or error)
     */
    function NewBlock($title, $contents, $displayTitle)
    {
        $this->gadget->CheckPermission('AddBlock');

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $request =& Jaws_Request::getInstance();
        $contents = $request->get(1, 'post', false);
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'AdminModel', 'Block');
        $res = $model->NewBlock($title, $contents, $displayTitle, $user);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->GetMessage(),
                                                         RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('BLOCKS_ADDED', "#$res"),
                                                     RESPONSE_NOTICE,
                                                     $res);
    }

    /**
     * Update a block
     *
     * @access  public
     * @param   int     $id             Block ID
     * @param   string  $title          Block title
     * @param   string  $contents       Block contents
     * @param   bool    $displayTitle   If true display block title
     * @return  array  Response array (notice or error)
     */
    function UpdateBlock($id, $title, $contents, $displayTitle)
    {
        $this->gadget->CheckPermission('EditBlock');

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $request =& Jaws_Request::getInstance();
        $contents = $request->get(2, 'post', false);
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'AdminModel', 'Block');
        $model->UpdateBlock($id, $title, $contents, $displayTitle, $user);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a block
     *
     * @access  public
     * @param   int     $id     Block ID
     * @return  array   Response array (notice or error)
     */
    function DeleteBlock($id)
    {
        $this->gadget->CheckPermission('DeleteBlock');
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'AdminModel', 'Block');
        $model->DeleteBlock($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Parse text
     *
     * @access  public
     * @param   string  $text    Input text (not parsed)
     * @return  string  Parsed text
     */
    function ParseText($text)
    {
        $request =& Jaws_Request::getInstance();
        $text = $request->get(0, 'post', false);
        $gadget = $GLOBALS['app']->LoadGadget('Blocks', 'AdminHTML');
        return $gadget->gadget->ParseText($text);
    }
}