<?php
/**
 * Blocks Gadget
 *
 * @category   GadgetModel
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Model extends Jaws_Gadget_Model
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
        $params       = array();
        $params['id'] = $id;
        $sql = '
            SELECT
                [id], [title], [contents], [display_title],
                [created_by], [createtime],
                [modified_by], [updatetime]
            FROM [[blocks]]
            WHERE [id] = {id}';

        $types = array('integer', 'text', 'text', 'boolean',
                       'integer', 'timestamp', 'integer', 'timestamp');
        $row = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('BLOCKS_ERROR_BLOCK_DOES_NOT_EXISTS', $id));
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
            $fields = '
                [id], [title]';
            $types = array('integer', 'text');
        } else {
            $fields = '
                [id], [title], [contents], [display_title],
                [created_by], [createtime],
                [modified_by], [updatetime]';
            $types = array('integer', 'text', 'text', 'boolean',
                           'integer', 'timestamp', 'integer', 'timestamp');
        }

        $sql = "
            SELECT
                $fields
            FROM [[blocks]]
            ORDER BY [title]";

        $result = $GLOBALS['db']->queryAll($sql, null, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }
}
