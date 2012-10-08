<?php
/**
 * BLOCKS AJAX API
 *
 * @category   Ajax
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlocksAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object  $model  Jaws_Model reference
     */
    function BlocksAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Get Block
     *
     * @access  public
     * @param   int      $id     Block ID
     * @return  mixed    Block data or False on error
     */
    function GetBlock($id)
    {
        $block = $this->_Model->GetBlock($id);
        if (Jaws_Error::IsError($b)) {
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
        $this->CheckSession('Blocks', 'AddBlock');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $id = $this->_Model->NewBlock($title, $contents, $displayTitle, $user);
        $response = $GLOBALS['app']->Session->PopLastResponse();
        // Little hack
        $response['id'] = $id;
        return $response;
    }

    /**
     * Update a block
     *
     * @access  public
     * @param   int     $id             Block ID
     * @param   string  $title          Block title
     * @param   string  $contents       Block contents
     * @param   bool    $displayTitle   If true display block title
     * @param   array  Response array (notice or error)
     */
    function UpdateBlock($id, $title, $contents, $displayTitle)
    {
        $this->CheckSession('Blocks', 'EditBlock');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $this->_Model->UpdateBlock($id, $title, $contents, $displayTitle, $user);
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
        $this->CheckSession('Blocks', 'DeleteBlock');
        $this->_Model->DeleteBlock($id);
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
        $gadget = $GLOBALS['app']->LoadGadget('Blocks', 'AdminHTML');
        return $gadget->ParseText($text, 'Blocks');
    }
}