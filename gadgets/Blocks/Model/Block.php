<?php
/**
 * Blocks Gadget
 *
 * @category   GadgetModel
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Model_Block extends Jaws_Gadget_Model
{
    /**
     * Get a Block
     *
     * @access  public
     * @param   int     $id     ID of the block to retrieve
     * @return  array   An array of the information of the Block or Jaws_Error on any error
     */
    function GetBlock($id)
    {
        $blocksTable = Jaws_ORM::getInstance()->table('blocks');
        $blocksTable->select(
            'id:integer', 'title', 'contents', 'display_title:boolean', 'created_by:integer',
            'modified_by:integer', 'createtime:timestamp', 'updatetime:timestamp'
        );
        return $blocksTable->where('id', $id)->fetchRow();
    }


    /**
     * Get all blocks
     *
     * @access  public
     * @param   bool    $simple     If true returns an array with id/title
     * @return  mixed   An array of blocks or Jaws_Error on any error
     */
    function GetBlocks($simple = false)
    {
        if ($simple) {
            $columns = array('id:integer', 'title');
        } else {
            $columns = array(
                'id:integer', 'title', 'contents', 'display_title:integer',
                'created_by:integer', 'modified_by:integer',
                'createtime:timestamp', 'updatetime:timestamp');
        }
        $blocksTable = Jaws_ORM::getInstance()->table('blocks');
        $blocksTable->select($columns)->orderBy('title asc');
        return $blocksTable->fetchAll();
    }
}