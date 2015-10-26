<?php
/**
 * Tags Model
 *
 * @category    GadgetModel
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Tags_Model_Tags extends Jaws_Gadget_Model
{
    /**
     * Generates a tag cloud
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   int     $user       Just show user's tags
     * @return  mixed   An array on success and Jaws_Error in case of errors
     */
    function GenerateTagCloud($gadget = '', $user = 0)
    {
        $table = Jaws_ORM::getInstance()->table('tags');
        $table->select('tags.id:integer', 'name', 'title', 'count(tags_references.gadget) as howmany:integer');
        $table->join('tags_references', 'tags_references.tag', 'tags.id', 'left');
        $table->where('tags_references.published', true);
        $table->and()->openWhere('tags_references.update_time', time(), '<')->or();
        $table->closeWhere('tags_references.update_time', null, 'is');
        $table->and()->where('tags.user', (int)$user);
        if (!empty($gadget)) {
            $table->and()->where('gadget', $gadget);
        }
        $table->groupBy('tags.id', 'tags.name', 'tags.title');
        return $table->orderBy('name asc')->fetchAll();
    }

    /**
     * Get tags
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $tag    Tag name
     * @param   int     $user   User owner of tag(0: for global tags)
     * @param   int     $limit  How many tags
     * @param   int     $offset Offset of data
     * @return  mixed   Array of Tags or Jaws_Error on failure
     */
    function GetTags($gadget, $tag, $user = 0, $limit = null, $offset = 0)
    {
        $tagsTable = Jaws_ORM::getInstance()->table('tags_references');
        $tagsTable->select('gadget', 'action', 'reference:integer');
        $tagsTable->join('tags', 'tags.id', 'tags_references.tag');
        $tagsTable->where('tags.user', (int)$user);
        if (!empty($gadget)) {
            $tagsTable->and()->where('gadget', $gadget);
        }
        $tagsTable->and()->where('tags.name', $tag);
        $tagsTable->and()->where('published', true);
        $tagsTable->orderBy('insert_time')->limit($limit, $offset);

        return $tagsTable->fetchAll();
    }

    /**
     * Get tags count
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $tag    Tag name
     * @param   int     $user   User owner of tag(0: for global tags)
     * @return  int     Count of Tags
     */
    function GetTagsCount($gadget, $tag, $user = 0)
    {
        $tagsTable = Jaws_ORM::getInstance()->table('tags_references');
        $tagsTable->select('count(tags_references.id):integer');
        $tagsTable->join('tags', 'tags.id', 'tags_references.tag');
        $tagsTable->where('tags_references.user', (int)$user);
        if (!empty($gadget)) {
            $tagsTable->and()->where('gadget', $gadget);
        }
        $tagsTable->and()->where('tags.name', $tag);
        $result = $tagsTable->and()->where('published', true)->fetchOne();

        return Jaws_Error::IsError($result)? 0 : $result;
    }

    /**
     * Get reference tags
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference ID
     * @param   int     $user   User owner of tag(0: for global tags)
     * @return  mixed   Array of Tags or Jaws_Error on failure
     */
    function GetReferenceTags($gadget, $action, $reference, $user = 0)
    {
        return Jaws_ORM::getInstance()
            ->table('tags_references')
            ->select('tags.name', 'tags.title')
            ->join('tags', 'tags.id', 'tags_references.tag')
            ->where('tags_references.user', (int)$user)
            ->and()->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->and()->where('reference', (int)$reference)
            ->and()->where('published', true)
            ->orderBy('insert_time')->fetchAll();
    }

    /**
     * Gets list of tag support gadgets
     *
     * @access  public
     * @return  array   List of tag-able gadgets
     */
    function GetTagableGadgets()
    {
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $info) {
            if (is_file(JAWS_PATH. "gadgets/$gadget/Hooks/Tags.php")) {
                $gadgets[$gadget] = $info['title'];
                continue;
            }
            unset($gadgets[$gadget]);
        }

        return $gadgets;
    }

}