<?php
/**
 * Blocks Gadget
 *
 * @category   GadgetModel
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2004-2022 Jaws Development Group
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
        return Jaws_ORM::getInstance()->table('blocks')
            ->select(
                'id:integer', 'title', 'summary', 'content', 'display_title:boolean',
                'inserted:integer', 'updated:integer'
            )
            ->where('id', $id)
            ->fetchRow();
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
                'id:integer', 'title', 'summary', 'content', 'display_title:integer',
                'inserted:integer', 'updated:integer'
            );
        }

        return Jaws_ORM::getInstance()->table('blocks')
            ->select($columns)
            ->orderBy('title asc')
            ->fetchAll();
    }

}