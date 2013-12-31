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
                    $tagId = $this->AddTag(array('name' => $tag, 'title' => $tag), $user);
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
     * @param   int     $user   User owner of tag(0: for global tags)
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function AddTag($data, $user = 0)
    {
        $data['user'] = (int)$user;
        $data['name'] = $this->GetRealFastUrl($data['name'], null, false);
        return Jaws_ORM::getInstance()->table('tags')->insert($data)->exec();
    }

    /**
     * Update a tag
     *
     * @access  public
     * @param   int     $id     Tag id
     * @param   array   $data   Tag data
     * @param   int     $user   User owner of tag(0: for global tags)
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function UpdateTag($id, $data, $user = 0)
    {
        $data['user'] = (int)$user;
        $data['name'] = $this->GetRealFastUrl($data['name'], null, false);
        $table = Jaws_ORM::getInstance()->table('tags');
        return $table->update($data)->where('id', $id)->exec();
    }

    /**
     * Delete tags
     *
     * @access  public
     * @param   array   $ids    Tags id
     * @param   int     $user   User owner of tag(0: for global tags)
     * @return  mixed   True/False or Jaws_Error on failure
     */
    function DeleteTags($ids, $user = 0)
    {
        // start Transaction
        $objORM = Jaws_ORM::getInstance()->beginTransaction();

        // internal query
        $objInternal = Jaws_ORM::getInstance()->table('tags');
        $objInternal->select('tags.id')
            ->where('tags.id', $ids, 'in')
            ->and()
            ->where('user', (int)$user)
            ->and()
            ->where('tags.id', array('tags_references.tag', 'expr'));

        // delete references
        $result = $objORM->table('tags_references')->delete()->where($objInternal, '', 'is not null')->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // delete tags
        $result = $objORM->table('tags')->delete()
            ->where('id', $ids, 'in')
            ->and()
            ->where('user', $user)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // commit Transaction
        $objORM->commit();
        return true;
    }

    /**
     * Merge tags
     *
     * @access  public
     * @param   array   $ids        Tags id
     * @param   string  $newName    New tag name
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  array   Response array (notice or error)
     */
    function MergeTags($ids, $newName, $user = 0)
    {
        $objORM = Jaws_ORM::getInstance()->table('tags');
        $newTag = $objORM->select('id:integer')->where('name', $newName)->and()->where('user', $user)->fetchOne();
        if (Jaws_Error::IsError($newTag)) {
            return $newTag;
        }

        //Start Transaction
        $objORM->beginTransaction();

        // Adding new tag if not exists
        if (empty($newTag)) {
            $newTag = $this->AddTag(array('name' => $newName, 'title' => $newName), $user);
            if (Jaws_Error::IsError($newTag)) {
                return $newTag;
            }
        }

        // Replacing tag of references with new tag
        $objInternal = Jaws_ORM::getInstance()->table('tags');
        $objInternal->select('tags.id')
            ->where('tags.id', $ids, 'in')
            ->and()
            ->where('user', $user)
            ->and()
            ->where('tags.id', array('tags_references.tag', 'expr'));
        $objORM->table('tags_references');
        $objORM->update(array('tag' => (int)$newTag));
        $result = $objORM->where($objInternal, '', 'is not null')->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Delete old tags
        $result = $objORM->table('tags')->delete()
            ->where('id', $ids, 'in')
            ->and()
            ->where('id', $newTag, '<>')
            ->and()
            ->where('user', $user)
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //Commit Transaction
        $objORM->commit();
        return true;
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
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTags($filters = array(), $limit = null, $offset = 0, $user = 0)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('tags.id:integer', 'name', 'title', 'count(tags_references.gadget) as usage_count:integer');
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
        $table->groupBy('tags.id', 'name', 'title')->limit($limit, $offset);
        $table->where('tags.user', (int)$user);
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

        return $table->fetchAll();
    }

    /**
     * Get tags count
     *
     * @access  public
     * @param   array   $filters    Data that will be used in the filter
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  mixed   Array of Tags info or Jaws_Error on failure
     */
    function GetTagsCount($filters = array(), $user = 0)
    {
        //TODO: we must improve performance!
        $table = Jaws_ORM::getInstance()->table('tags');

        $table->select('count(tags.id):integer');
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
        $table->groupBy('tags.id');
        $table->where('tags.user', (int)$user);
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
        return Jaws_Error::IsError($result)? 0 : $result;
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