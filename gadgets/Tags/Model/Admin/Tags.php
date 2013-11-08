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
     * Update a gadget's tags item
     *
     * @access  public
     * @param   string          $gadget         gadget name
     * @param   string          $action         action name
     * @param   int             $reference      reference
     * @param   bool            $published      reference published?
     * @param   int             $update_time    reference update time
     * @param   string/array    $tags           comma separated of tags name (tag1, tag2, tag3, ...)
     * @param   bool            $global         is global tags?
     * @return  mixed       Array of Tag info or Jaws_Error on failure
     */
    function UpdateTagsItems($gadget, $action , $reference, $published, $update_time, $tags, $global = true)
    {
        if (empty($tags)) {
            return false;
        }

        if (!is_array($tags)) {
            $tags = array_filter(array_map(array($GLOBALS['app']->UTF8, 'trim'), explode(',', $tags)));
        }

        $oldTagsInfo = $this->GetItemTags(
                                           array('gadget' => $gadget, 'action' => $action, 'reference' => $reference),
                                           false,
                                           $global);
        $oldTags = array();
        $oldTagsId = array();
        foreach ($oldTagsInfo as $tagInfo) {
            $oldTags[] = $tagInfo['name'];
            $oldTagsId[] = $tagInfo['item_id'];
        }

        // First - Update old tag info
        $table = Jaws_ORM::getInstance()->table('tags_items');
        $table->update(array('published' => $published, 'update_time' => $update_time));
        $table->where('gadget', $gadget);
        $table->and()->where('action', $action);
        $table->and()->where('reference', $reference);
        $table->and()->where('id', $oldTagsId, 'in');
        $table->exec();

        $to_be_added_tags = array_diff($tags, $oldTags);
        $res = $this->AddTagsToItem($gadget, $action, $reference, $published, $update_time, $to_be_added_tags, $global);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $to_be_removed_tags = array_diff($oldTags, $tags);
        $res = $this->RemoveTagsFromItem($gadget, $action, $reference, $to_be_removed_tags);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

    }

/**
     * Add tags to item
     *
     * @access  public
     * @param   string          $gadget         gadget name
     * @param   string          $action         action name
     * @param   int             $reference      reference
     * @param   bool            $published      reference published?
     * @param   int             $update_time    reference update time
     * @param   string/array    $tagsString     comma separated of tags name (tag1, tag2, tag3, ...)
     * @param   bool            $global         is global tag?
     * @return  mixed           Array of Tag info or Jaws_Error on failure
 */
    function AddTagsToItem($gadget, $action, $reference, $published, $update_time, $tags, $global = true)
    {
        if (empty($tags) || count($tags) < 1) {
            return true;
        }

        if (!is_array($tags)) {
            $tags = array_filter(array_map(array($GLOBALS['app']->UTF8, 'trim'), explode(',', $tags)));
        }

        $systemTags = array();
        foreach($tags as $tag){
            $table = Jaws_ORM::getInstance()->table('tags');
            $tagId = $table->select('id:integer')->where('name', $tag)->fetchOne();
            if(!empty($tagId)) {
                $systemTags[$tag] = $tagId;
            } else {
                //Add an new tag
                $systemTags[$tag] = $this->AddTag(array('name' => $tag), $global);
            }
        }

        $table = Jaws_ORM::getInstance()->table('tags_items');
        $tData = array();
        foreach($systemTags as $tagName=>$tagId) {
            $data = array($gadget , $action, $reference, $tagId, time(), $published, $update_time);
            $tData[] = $data;
        }

        $column = array('gadget', 'action', 'reference', 'tag', 'insert_time', 'published', 'update_time');
        $res = $table->insertAll($column, $tData)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

/**
     * Remove tags from item
     *
     * @access  public
     * @param   string      $gadget         gadget name
     * @param   string      $action         action name
     * @param   int         $reference      reference
     * @param   array       $tags           array of tags name
     * @return  mixed       Array of Tag info or Jaws_Error on failure
     */
    function RemoveTagsFromItem($gadget, $action ,$reference, $tags)
    {
        if(empty($tags) || count($tags)<1) {
            return true;
        }
        $table = Jaws_ORM::getInstance()->table('tags');
        $tagsId = $table->select('id:integer')->where('name', $tags, 'in')->fetchColumn();

        $table = Jaws_ORM::getInstance()->table('tags_items');

        $table->delete()->where('gadget', $gadget)->and()->where('action', $action);
        $table->and()->where('reference', $reference)->and()->where('tag', $tagsId, 'in');
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return $res;
    }

    /**
     * Add an new tag
     *
     * @access  public
     * @param   array   $data   Tag data
     * @param   bool    $global Is global tag?
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function AddTag($data, $global = true)
    {
        $data['user'] = 0;
        if(!$global) {
            $data['user'] = $GLOBALS['app']->Session->GetAttribute('user');
        }
        if (empty($data['title'])) {
            $data['title'] = $data['name'];
        }
        $data['name'] = $this->GetRealFastUrl($data['name'], null, false);

        // check duplicated tag
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('count(id)')->where('name', $data['name'])->and()->where('user', $data['user']);
        $tag = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }
        if ($tag > 0) {
            return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $data['name']), _t('TAGS_NAME'));
        }

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Update a tag
     *
     * @access  public
     * @param   int     $id     Tag id
     * @param   array   $data   Tag data
     * @param   bool    $global Is global tag?
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function UpdateTag($id, $data, $global = true)
    {
        $data['name'] = $this->GetRealFastUrl($data['name'], null, false);

        // check duplicated tag
        $oldTag = $this->GetTag($id);
        if ($oldTag['name'] != $data['name']) {
            $user = 0;
            if(!$global) {
                $user = $GLOBALS['app']->Session->GetAttribute('user');
            }
            $table = Jaws_ORM::getInstance()->table('tags');
            $table->select('count(id)')->where('name', $data['name'])->and()->where('user', $user);
            $tag = $table->fetchOne();
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error($result->getMessage(), 'SQL');
            }
            if ($tag > 0) {
                return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $data['name']), _t('TAGS_NAME'));
            }
        }

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Delete Item(s) tags
     *
     * @access  public
     * @param   string      $gadget         gadget name
     * @param   string      $action         action name
     * @param   int/array   $references     references
     * @return  mixed   True/False or Jaws_Error on failure
     */
    function DeleteItemTags($gadget, $action, $references)
    {
        if(!is_array($references)) {
            $references = array($references);
        }
        $table = Jaws_ORM::getInstance()->table('tags_items');

        $table->delete()->where('gadget', $gadget);
        $table->and()->where('action', $action);
        $table->and()->where('reference', $references, 'in');

        $result = $table->exec();
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
     * @return  mixed   True/False or Jaws_Error on failure
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
     * @param   bool        $global     Is global?
     * @return  array   Response array (notice or error)
     */
    function MergeTags($ids, $newName, $global = true)
    {
        // check duplicated tag
        $user = 0;
        if (!$global) {
            $user = $GLOBALS['app']->Session->GetAttribute('user');
        }
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('count(id)')->where('name', $newName)->and()->where('user', $user);
        $tag = $table->and()->where('id', $ids, 'not in')->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }
        if ($tag > 0) {
            return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $newName), _t('TAGS_NAME'));
        }

        $data = array();
        $data['title'] = $newName;
        $data['name'] = $this->GetRealFastUrl($newName, null, false);

        $table = Jaws_ORM::getInstance()->table('tags');
        //Start Transaction
        $table->beginTransaction();

        $firstID = $ids[0];
        unset($ids[0]);

        //Delete extra tags
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        //Update first tag
        $this->UpdateTag($firstID, $data, $global);

        //Update tag items
        $table = Jaws_ORM::getInstance()->table('tags_items');
        $table->update(array('tag' => $firstID))->where('tag', $ids, 'in')->exec();

        $keepIds = Jaws_ORM::getInstance()->table('tags_items')
            ->select('tags_items.id:integer')->groupBy('gadget', 'action', 'reference', 'tag', 'user')
            ->join('tags', 'tags.id', 'tags_items.tag')
            ->having('count(tags_items.id)', '1', '>')->fetchColumn();

        //Delete duplicated items
        // We need to find all duplicated items for deleting
        $table = Jaws_ORM::getInstance()->table('tags_items', 'item1');
        $table->distinct();
        $table->select('item1.id:integer');
        $table->join('tags_items as item2', 'item2.tag', 'item1.tag');
        $table->join('tags', 'tags.id', 'item1.tag');
        $table->and()->where('item1.gadget', array('item2.gadget', 'expr'));
        $table->and()->where('item1.action', array('item2.action', 'expr'));
        $table->and()->where('item1.reference', array('item2.reference', 'expr'));
        $table->and()->where('item1.id', array('item2.id', 'expr'), '<>');
        $table->and()->where('tags.user', $user);
        $table->and()->where('item1.id', $keepIds, 'not in');
        $items = $table->fetchColumn();
        if (Jaws_Error::IsError($items)) {
            return new Jaws_Error($items->getMessage(), 'SQL');
        }

        // delete duplicated tags items
        if (count($items) > 0) {
            $table = Jaws_ORM::getInstance()->table('tags_items');
            $res = $table->delete()->where('id', $items, 'in')->exec();
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
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
        $table->select('name', 'user:integer', 'title', 'description', 'meta_keywords', 'meta_description');
        $result = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get tags
     *
     * @access  public
     * @param   array   $filters           Data that will be used in the filter
     * @param   bool    $justReturnName    Data that will be used in the filter
     * @param   bool    $global            Just fetch global tags?
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetItemTags($filters = array(), $justReturnName = false, $global = true)
    {
        $table = Jaws_ORM::getInstance()->table('tags');

        $columns = array('tags.name');
        if (!$justReturnName) {
            $columns[] = 'tags_items.id as item_id:integer';
            $columns[] = 'tags.id as tag_id:integer';
        }

        $table->select($columns);
        $table->join('tags_items', 'tags_items.tag', 'tags.id');

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
            if (array_key_exists('reference', $filters) && !empty($filters['reference'])) {
                $table->and()->where('reference', $filters['reference']);
            }
        }

        if ($global) {
            $table->and()->where('tags.user', 0);
        } else {
            $table->and()->where('tags.user', $GLOBALS['app']->Session->GetAttribute('user'));
        }


        if ($justReturnName) {
            $result = $table->fetchColumn();
        } else {
            $result = $table->fetchAll();
        }
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
     * @param   bool    $global     just get global tags?
     *                              (null : get all tags, true : just get global tags, false : just get user tags)
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTags($filters = array(), $limit = null, $offset = 0, $orderBy = 0, $global = null)
    {
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('tags.id:integer', 'name', 'title', 'count(tags_items.gadget) as usage_count:integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');
        $table->groupBy('tags.id')->limit($limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            if (array_key_exists('name', $filters) && !empty($filters['name'])) {
                $table->and()->openWhere('name', '%' . $filters['name'] . '%', 'like')->or();
                $table->closeWhere('title', '%' . $filters['name'] . '%', 'like');
            }
            if (array_key_exists('gadget', $filters) && !empty($filters['gadget'])) {
                $table->and()->where('gadget', $filters['gadget']);
            }
            if (array_key_exists('action', $filters) && !empty($filters['action'])) {
                $table->and()->where('action', $filters['action']);
            }
        }

        if($global===true) {
            $table->and()->where('tags.user', 0);
        } elseif ($global===false) {
            $table->and()->where('tags.user', $GLOBALS['app']->Session->GetAttribute('user'));
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
     * @param   bool    $global     just get global tags?
     *                              (null : get all tags, true : just get global tags, false : just get user tags)\
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTagsCount($filters = array(), $global = null)
    {
        //TODO: we must improve performance!
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('count(tags.id):integer');
        $table->join('tags_items', 'tags_items.tag', 'tags.id', 'left');
        $table->groupBy('tags.id');

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

        if($global===true) {
            $table->and()->where('tags.user', 0);
        } elseif ($global===false) {
            $table->and()->where('tags.user', $GLOBALS['app']->Session->GetAttribute('user'));
        }

        $result = $table->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return count($result);
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