<?php
/**
 * Tags Gadget Admin
 *
 * @category    GadgetModel
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Tags_Model_Admin_Tags extends Jaws_Gadget_Model
{
    /**
     * Update a tag
     *
     * @access  public
     * @param   int     $id   Tag id
     * @param   string  $name   Tag name
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function UpdateTag($id, $name)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->update(array('name' => $name))->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Delete tags
     *
     * @access  public
     * @param   array   $ids    Tags id
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function DeleteTags($ids)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a tag info
     *
     * @access  public
     * @param   int     $id   Tag id
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function GetTag($id)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->select('name', 'user:integer')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get tags
     *
     * @access  public
     * @param   array   $filters    Data that will be used in the filter
     * @param   int     $limit      How many tags
     * @param   mixed   $offset     Offset of data
     * @param   int     $orderBy    The column index which the result must be sorted by
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTags($filters = array(), $limit = 15, $offset = 0, $orderBy = 0)
    {
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('tags.id:integer', 'name', 'count(tags.id) as usage_count:integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');
        $table->groupBy('tags.id')->limit($limit, $offset);
        $orders = array(
            'insert_time asc',
            'insert_time desc',
        );
        $orderBy = (int)$orderBy;
        $orderBy = $orders[($orderBy > 1)? 1 : $orderBy];

        $result = $table->orderBy($orderBy)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }


}