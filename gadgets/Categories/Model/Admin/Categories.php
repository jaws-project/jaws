<?php
/**
 * Categories Gadget
 *
 * @category    GadgetModel
 * @package     Categories
 */
class Categories_Model_Admin_Categories extends Jaws_Gadget_Model
{
    /**
     * Get a list of the Categories
     *
     * @access  public
     * @param   array    $filters   category filters
     * @param   bool|int $limit     Count of categories to be returned
     * @param   int      $offset    Offset of data array
     * @return  mixed   Array of Categories or Jaws_Error on failure
     */
    function GetCategories($filters = null, $limit = false, $offset = null)
    {
        $categoriesTable = Jaws_ORM::getInstance()->table('categories');
        $categoriesTable->select(
            'id:integer', 'gadget', 'action', 'title', 'description',
            'meta_title', 'meta_keywords', 'meta_description', 'insert_time:integer'
        );
        $categoriesTable->orderBy('categories.insert_time desc');
        $categoriesTable->limit((int)$limit, $offset);

        if (!empty($filters) && count($filters) > 0) {
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $categoriesTable->and()->where('categories.gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && !empty($filters['action'])) {
                $categoriesTable->and()->where('categories.action', $filters['action'], 'like');
            }
            // user
            if (isset($filters['term']) && !empty($filters['term'])) {
                $categoriesTable->and()->where('title', $filters['term'], 'like');
            }
        }

        return $categoriesTable->fetchAll();
    }

    /**
     * Gets categories count
     *
     * @access  public
     * @param   array   $filters   category filters
     * @return  mixed   Count of available categories and Jaws_Error on failure
     */
    function GetCategoriesCount($filters = null)
    {
        $categoriesTable = Jaws_ORM::getInstance()->table('categories');
        $categoriesTable->select('count(id):integer');

        if (!empty($filters) && count($filters) > 0) {
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $categoriesTable->and()->where('categories.gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && !empty($filters['action'])) {
                $categoriesTable->and()->where('categories.action', $filters['action'], 'like');
            }
            // user
            if (isset($filters['term']) && !empty($filters['term'])) {
                $categoriesTable->and()->where('title', $filters['term'], 'like');
            }
        }

        return $categoriesTable->fetchOne();
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
        return Jaws_ORM::getInstance()->table('categories')
            ->select(
                'id:integer', 'gadget', 'action', 'title', 'description',
                'meta_title', 'meta_keywords', 'meta_description', 'insert_time:integer'
            )->where('id', (int)$id)->fetchRow();
    }

    /**
     * Insert a category
     *
     * @access  public
     * @param   array   $data      The category data
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function InsertCategory($data)
    {
        $data['insert_time'] = time();
        return Jaws_ORM::getInstance()->table('categories')->insert($data)->exec();
    }

    /**
     * Updates a category
     *
     * @access  public
     * @param   int     $id        Category ID
     * @param   array   $data      The category data
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateCategory($id, $data)
    {
        return Jaws_ORM::getInstance()->table('categories')->update($data)->where('id', $id)->exec();
    }

    /**
     * Delete a category
     *
     * @access  public
     * @param   int     $id      Category ID
     * @return  mixed   Array of Categories or Jaws_Error on failure
     */
    function DeleteCategory($id)
    {
        return Jaws_ORM::getInstance()->table('categories')->delete()->where('id', $id)->exec();
    }
}