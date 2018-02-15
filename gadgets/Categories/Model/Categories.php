<?php
/**
 * Categories Gadget
 *
 * @category    GadgetModel
 * @package     Categories
 */
class Categories_Model_Categories extends Jaws_Gadget_Model
{
    /**
     * Get a list of  categories
     *
     * @access  public
     * @param   string      $gadget    Gadget name
     * @param   string      $action    Action name
     * @param   bool|int    $limit     Count of categories to be returned
     * @param   int         $offset    Offset of data array
     * @return  mixed   Array of Categories or Jaws_Error on failure
     */
    function GetCategories($gadget, $action, $limit = false, $offset = null)
    {
        return Jaws_ORM::getInstance()->table('categories')->select(
            'id:integer', 'title', 'description', 'meta_title', 'meta_keywords',
            'meta_description', 'insert_time:integer', 'published:boolean'
        )->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->orderBy('insert_time desc')
            ->limit((int)$limit, $offset)->fetchAll();
    }

    /**
     * Gets categories count
     *
     * @access  public
     * @param   string      $gadget    Gadget name
     * @param   string      $action    Action name
     * @return  mixed   Count of available categories and Jaws_Error on failure
     */
    function GetCategoriesCount($gadget, $action)
    {
        return Jaws_ORM::getInstance()
            ->table('categories')
            ->select('count(id):integer')
            ->where('gadget', $gadget)
            ->and()->where('action', $action)
            ->fetchOne();
    }

    /**
     * Get info of a Category
     *
     * @access  public
     * @param   int     $id      Category ID
     * @return  mixed   Array of Categories or Jaws_Error on failure
     */
    function GetCategory($id)
    {
        return Jaws_ORM::getInstance()->table('categories')->select(
            'id:integer', 'title', 'description', 'meta_title', 'meta_keywords',
            'meta_description', 'insert_time:integer', 'published:boolean'
        )->where('id', (int) $id)->fetchRow();
    }

}