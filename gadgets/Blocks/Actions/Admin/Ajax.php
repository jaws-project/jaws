<?php
/**
 * BLOCKS AJAX API
 *
 * @category   Ajax
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get Block
     *
     * @access  public
     * @internal param  int $id Block ID
     * @return  mixed   Block data or False on error
     */
    function GetBlock()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Block');
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
     * @internal param  string  $title      Block title
     * @internal param  string  $summary    Block summary
     * @internal param  string  $content    Block content
     * @internal param  bool    $displayTitle If true display block title
     * @return  array   Response array (notice or error)
     */
    function NewBlock()
    {
        $this->gadget->CheckPermission('AddBlock');

        @list($title, $contents, $displayTitle) = $this->gadget->request->fetchAll('post');
        $summary = $this->gadget->request->fetch(1, 'post', 'strip_crlf');
        $content = $this->gadget->request->fetch(2, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Block');
        $res = $model->NewBlock($title, $summary, $content, $displayTitle);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(),
                                                         RESPONSE_ERROR);
        }

        return $this->gadget->session->response($this::t('ADDED', "#$res"),
                                                     RESPONSE_NOTICE,
                                                     $res);
    }

    /**
     * Update a block
     *
     * @access  public
     * @internal param  int     $id         Block ID
     * @internal param  string  $title      Block title
     * @internal param  string  $summary    Block summary
     * @internal param  string  $content    Block content
     * @internal param  bool    $displayTitle If true display block title
     * @return  array  Response array (notice or error)
     */
    function UpdateBlock()
    {
        $this->gadget->CheckPermission('EditBlock');

        @list($id, $title, $contents, $displayTitle) = $this->gadget->request->fetchAll('post');
        $summary = $this->gadget->request->fetch(2, 'post', 'strip_crlf');
        $content = $this->gadget->request->fetch(3, 'post', 'strip_crlf');
        $model = $this->gadget->model->loadAdmin('Block');
        $model->UpdateBlock($id, $title, $summary, $content, $displayTitle);
        return $this->gadget->session->pop();
    }

    /**
     * Delete a block
     *
     * @access  public
     * @internal param  int $id Block ID
     * @return  array   Response array (notice or error)
     */
    function DeleteBlock()
    {
        $this->gadget->CheckPermission('DeleteBlock');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Block');
        $model->DeleteBlock($id);
        return $this->gadget->session->pop();
    }

    /**
     * Parse text
     *
     * @access  public
     * @internal param  string $text Input text (not parsed)
     * @return  string  Parsed text
     */
    function ParseText()
    {
        $text = $this->gadget->request->fetch(0, 'post', 'strip_crlf');
        return $this->gadget->plugin->parseAdmin($text);
    }
}