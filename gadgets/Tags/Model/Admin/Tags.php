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
     * Update a gadget reference tags
     *
     * @access  public
     * @param   string          $gadget         gadget name
     * @param   string          $action         action name
     * @param   int             $reference      reference
     * @param   bool            $published      reference published?
     * @param   int             $update_time    reference update time
     * @param   string/array    $tags           comma separated of tags name (tag1, tag2, tag3, ...)
     * @param   int             $user           User owner of tag(0: for global tags)
     * @return  mixed           Array of Tag info or Jaws_Error on failure
     */
    function UpdateReferenceTags($gadget, $action , $reference, $published, $update_time, $tags, $user = 0)
    {
        $update_time = empty($update_time)? time() : $update_time;
        // First - Update old tag info
        $table = Jaws_ORM::getInstance()->table('tags_references');
        $table->update(array('published' => $published, 'update_time' => $update_time));
        $table->where('gadget', $gadget);
        $table->and()->where('action', $action);
        $table->and()->where('reference', $reference);
        $table->exec();

        $oldTags = $this->GetReferenceTags($gadget, $action, $reference, $user);
        if (Jaws_Error::IsError($oldTags)) {
            return $oldTags;
        }

        if (!is_array($tags)) {
            $tags = array_filter(array_map('Jaws_UTF8::trim', explode(',', $tags)));
        }
        $to_be_added_tags = array_diff($tags, $oldTags);
        $res = $this->InsertReferenceTags(
            $gadget, $action, $reference, $published,
            $update_time, $to_be_added_tags, $user
        );
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $to_be_removed_tags = array_diff($oldTags, $tags);
        $res = $this->DeleteReferenceTags($gadget, $action, $reference, $to_be_removed_tags);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Inserts gadget reference tags
     *
     * @access  public
     * @param   string          $gadget         gadget name
     * @param   string          $action         action name
     * @param   int             $reference      reference
     * @param   bool            $published      reference published?
     * @param   int             $update_time    reference update time
     * @param   string/array    $tagsString     comma separated of tags name (tag1, tag2, tag3, ...)
     * @param   int             $user           User owner of tag(0: for global tags)
     * @return  mixed           Array of Tag info or Jaws_Error on failure
     */
    function InsertReferenceTags($gadget, $action, $reference, $published, $update_time, $tags, $user = 0)
    {
        if (!is_array($tags)) {
            $tags = array_filter(array_map('Jaws_UTF8::trim', explode(',', $tags)));
        }
        if (empty($tags)) {
            return true;
        }

        $systemTags = array();
        $table = Jaws_ORM::getInstance()->table('tags');
        foreach($tags as $tag){
            $tagId = $table->select('id:integer')
                ->where('name', $tag)
                ->and()
                ->where('user', $user)
                ->fetchOne();
            if (!Jaws_Error::IsError($tagId)) {
                if (empty($tagId)) {
                    $tagId = $this->AddTag(array('name' => $tag), $user == 0);
                    if (Jaws_Error::IsError($tagId)) {
                        continue;
                    }
                }
                $systemTags[$tag] = $tagId;
            }
        }

        $tData = array();
        foreach($systemTags as $tagName => $tagId) {
            $tData[] = array($gadget , $action, $reference, $tagId, time(), $published, $update_time);
        }

        $column = array('gadget', 'action', 'reference', 'tag', 'insert_time', 'published', 'update_time');
        $table = Jaws_ORM::getInstance()->table('tags_references');
        $res = $table->insertAll($column, $tData)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage());
        }

        return $res;
    }

    /**
     * Delete gadget reference tags
     *
     * @access  public
     * @param   string          $gadget     gadget name
     * @param   string          $action     action name
     * @param   int|array       $references references
     * @param   string|array    $tags       array|string of tags names
     * @return  mixed           Array of Tag info or Jaws_Error on failure
     */
    function DeleteReferenceTags($gadget, $action ,$references, $tags = null)
    {
        if (!is_null($tags)) {
            if (!is_array($tags)) {
                $tags = array_filter(array_map('Jaws_UTF8::trim', explode(',', $tags)));
            }
            if (empty($tags)) {
                return false;
            }
        }

        if (!is_array($references)) {
            $references = array((int)$references);
        }

        $table = Jaws_ORM::getInstance()->table('tags_references')->delete()
            ->where('gadget', $gadget)
            ->and()
            ->where('action', $action)
            ->and()
            ->where('reference', $references, 'in');
        if (!is_null($tags)) {
            $table->join('tags', 'tags.id', 'tags_references.tag');
            $table->and()->where('tags.name', $tags, 'in');
        }

        return $table->exec();
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
        if (Jaws_Error::IsError($tag)) {
            return new Jaws_Error($tag->getMessage());
        }
        if ($tag > 0) {
            return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $data['name']));
        }

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
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
                return new Jaws_Error($result->getMessage());
            }
            if ($tag > 0) {
                return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $data['name']));
            }
        }

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
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
        $table = Jaws_ORM::getInstance()->table('tags_references');
        //Start Transaction
        $table->beginTransaction();

        $table->delete()->where('tag', $ids, 'in')->exec();

        $table = Jaws_ORM::getInstance()->table('tags');
        $result = $table->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
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
            return new Jaws_Error($result->getMessage());
        }
        if ($tag > 0) {
            return new Jaws_Error(_t('TAGS_ERROR_TAG_ALREADY_EXIST', $newName));
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
            return new Jaws_Error($result->getMessage());
        }

        //Update first tag
        $this->UpdateTag($firstID, $data, $global);

        //Update tag items
        $table = Jaws_ORM::getInstance()->table('tags_references');
        $table->update(array('tag' => $firstID))->where('tag', $ids, 'in')->exec();

        $keepIds = Jaws_ORM::getInstance()->table('tags_references')
            ->select('tags_references.id:integer')->groupBy('gadget', 'action', 'reference', 'tag', 'user')
            ->join('tags', 'tags.id', 'tags_references.tag')
            ->having('count(tags_references.id)', '1', '>')->fetchColumn();

        //Delete duplicated items
        // We need to find all duplicated items for deleting
        $table = Jaws_ORM::getInstance()->table('tags_references', 'item1');
        $table->distinct();
        $table->select('item1.id:integer');
        $table->join('tags_references as item2', 'item2.tag', 'item1.tag');
        $table->join('tags', 'tags.id', 'item1.tag');
        $table->and()->where('item1.gadget', array('item2.gadget', 'expr'));
        $table->and()->where('item1.action', array('item2.action', 'expr'));
        $table->and()->where('item1.reference', array('item2.reference', 'expr'));
        $table->and()->where('item1.id', array('item2.id', 'expr'), '<>');
        $table->and()->where('tags.user', $user);
        $table->and()->where('item1.id', $keepIds, 'not in');
        $items = $table->fetchColumn();
        if (Jaws_Error::IsError($items)) {
            return new Jaws_Error($items->getMessage());
        }

        // delete duplicated tags items
        if (count($items) > 0) {
            $table = Jaws_ORM::getInstance()->table('tags_references');
            $res = $table->delete()->where('id', $items, 'in')->exec();
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage());
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
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get reference tags
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetReferenceTags($gadget, $action, $reference, $user = 0)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('tags.name');
        $table->join('tags_references', 'tags_references.tag', 'tags.id');
        return $table->where('tags.user', (int)$user)
            ->and()->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->and()->where('reference', (int)$reference)
            ->fetchColumn();
    }

    /**
     * Get tags
     *
     * @access  public
     * @param   array   $filters    Data that will be used in the filter
     * @param   int     $limit      How many tags
     * @param   mixed   $offset     Offset of data
     * @param   bool    $global     just get global tags?
     *                              (null : get all tags, true : just get global tags, false : just get user tags)
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTags($filters = array(), $limit = null, $offset = 0, $global = null)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('tags.id:integer', 'name', 'title', 'count(tags_references.gadget) as usage_count:integer');
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
        $table->groupBy('tags.id', 'name', 'title')->limit($limit, $offset);

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

        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
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
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
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
            return new Jaws_Error($result->getMessage());
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

        $table->select('tags_references.action');
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
        $result = $table->groupBy('tags_references.action')->where('tags_references.gadget', $gadget)->fetchColumn();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

}