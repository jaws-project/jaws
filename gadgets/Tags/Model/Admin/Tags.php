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
     * Add an new tag
     *
     * @access  public
     * @param   string  $name   Tag name
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function AddTag($name)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->insert(array('name' => $name, 'user' => 0))->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

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
        $table = Jaws_ORM::getInstance()->table('tags_items');
        //Start Transaction
        $table->beginTransaction();

        $table->delete()->where('tag', $ids, 'in')->exec();

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        //Commit Transaction
        $table->commit();
        return $result;
    }

    /**
     * Merge tags
     *
     * @access  public
     * @param   array       $ids        Tags id
     * @param   string      $newName    New tag name
     * @return  array   Response array (notice or error)
     */
    function MergeTags($ids, $newName)
    {
        $table = Jaws_ORM::getInstance()->table('tags_items');
        //Start Transaction
        $table->beginTransaction();

        // Add new tag
        $newId = $this->AddTag($newName);

        //Update tag items
        $res = $table->update(array('tag' => $newId))->where('tag', $ids, 'in')->exec();

        //Delete old tags
        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        //Commit Transaction
        $table->commit();
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

        $table->select('tags.id:integer', 'name', 'count(tags_items.gadget) as usage_count:integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');
        $table->groupBy('tags.id')->limit($limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            if (array_key_exists('name', $filters) && !empty($filters['name'])) {
                $table->and()->where('name', '%' . $filters['name'] . '%', 'like');
            }
            if (array_key_exists('gadget', $filters) && !empty($filters['gadget'])) {
                $table->and()->where('gadget', $filters['gadget']);
            }
            if (array_key_exists('action', $filters) && !empty($filters['action'])) {
                $table->and()->where('action', $filters['action']);
            }
        }

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

    /**
     * Get tags count
     *
     * @access  public
     * @param   array   $filters    Data that will be used in the filter
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTagsCount($filters = array())
    {
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('count(tags.id):integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');

        if (!empty($filters) && count($filters) > 0) {
            if (array_key_exists('name', $filters) && !empty($filters['name'])) {
                $table->and()->where('name', '%' . $filters['name'] . '%', 'like');
            }
            if (array_key_exists('gadget', $filters) && !empty($filters['gadget'])) {
                $table->and()->where('gadget', $filters['gadget']);
            }
            if (array_key_exists('action', $filters) && !empty($filters['action'])) {
                $table->and()->where('action', $filters['action']);
            }
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a gadget available actions
     *
     * @access   public
     * @param    string  $gadget Gadget name
     * @return   array   gadget actions
     */
    function GetGadgetActions($gadget)
    {
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('tags_items.action');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');
        $result = $table->groupBy('tags_items.action')->where('tags_items.gadget', $gadget)->fetchColumn();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

}