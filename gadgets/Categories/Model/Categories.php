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
     * Returns array of categories
     *
     * @access  public
     * @param   array       $interface  Gadget connection interface
     * @param   bool|int    $limit      Count of categories to be returned
     * @param   int         $offset     Offset of data array
     * @return  array   List of files info or Jaws_Error on error
     */
    function getCategories($interface, $limit = false, $offset = null)
    {
        $data = array(
            'gadget'    => '',
            'action'    => '',
        );
        $interface = array_merge($data, $interface);

        return Jaws_ORM::getInstance()->table('categories')
            ->select(
                'id:integer', 'title', 'description', 'meta_title', 'meta_keywords',
                'meta_description', 'insert_time:integer', 'published:boolean'
            )->where('gadget', $interface['gadget'])
            ->and()
            ->where('action', $interface['action'])
            ->orderBy('id desc')
            ->limit((int)$limit, $offset)
            ->fetchAll();
    }

    /**
     * Gets categories count
     *
     * @access  public
     * @param   array   $interface  Gadget connection interface
     * @return  mixed   Count of available categories or Jaws_Error on failure
     */
    function getCategoriesCount($interface)
    {
        $data = array(
            'gadget'    => '',
            'action'    => '',
        );
        $interface = array_merge($data, $interface);

        return Jaws_ORM::getInstance()
            ->table('categories')
            ->select('count(id):integer')
            ->where('gadget', $interface['gadget'])
            ->and()->where('action', $interface['action'])
            ->fetchOne();
    }

    /**
     * Returns array of categories
     *
     * @access  public
     * @param   array       $interface  Gadget connection interface
     * @return  array   List of files info or Jaws_Error on error
     */
    function getReferenceCategories($interface)
    {
        $data = array(
            'gadget'    => '',
            'action'    => '',
            'reference' => 0,
        );
        $interface = array_merge($data, $interface);

        return Jaws_ORM::getInstance()->table('categories_references')
            ->select('category:integer')
            ->join('categories', 'categories_references.category', 'categories.id')
            ->where('gadget', $interface['gadget'])
            ->and()
            ->where('action', $interface['action'])
            ->and()
            ->where('reference', $interface['reference'])
            ->fetchColumn();
    }

    /**
     * Get info of a Category
     *
     * @access  public
     * @param   int     $id      Category ID
     * @return  mixed   Array of Categories or Jaws_Error on failure
     */
    function getCategory($id)
    {
        return Jaws_ORM::getInstance()->table('categories')->select(
            'id:integer', 'title', 'description', 'meta_title', 'meta_keywords',
            'meta_description', 'insert_time:integer', 'published:boolean'
        )->where('id', (int) $id)->fetchRow();
    }

    /**
     * Insert new categories
     *
     * @access  public
     * @param   array   $interface  Gadget connection interface
     * @param   array   $titles     Titles of new categories 
     * @return  array   Array of categories ID
     */
    function insertCategories($interface, $titles)
    {
        if (empty($titles)) {
            return array();
        }

        $data = array(
            'gadget'    => '',
            'action'    => '',
        );
        // remove invalid interface keys
        $interface = array_intersect_key($interface, $data);
        // set undefined keys by default values
        $data = array_merge($data, $interface);

        $categories = array();
        $objTable = Jaws_ORM::getInstance()->table('categories');
        foreach ($titles as $title) {
            $data['title'] = $title;
            $result = $objTable->insert($data)->exec();  
            if (!Jaws_Error::IsError($result)) {
                $categories[] = $result;
            }
        }

        return $categories;
    }

    /**
     * Set reference categories
     *
     * @access  public
     * @param   array   $interface      Gadget connection interface
     * @param   array   $new_categories New categories
     * @param   array   $del_categories Delete categories
     * @return  bool    True if insert successfully otherwise False
     */
    function setReferenceCategories($interface, $new_categories, $del_categories)
    {
        $data = array(
            'gadget'    => '',
            'action'    => '',
            'reference' => 0,
        );
        $interface = array_merge($data, $interface);

        $objTable = Jaws_ORM::getInstance();
        // delete reference categories
        $result = $objTable->table('categories_references')
            ->delete()
            ->where('category', $del_categories, 'in')
            ->and()
            ->where('reference', (int)$interface['reference'])
            ->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // insert new reference categories
        foreach ($new_categories as $category) {
            $objTable->table('categories_references')
            ->insert(
                array(
                    'category'  => (int)$category,
                    'reference' => (int)$interface['reference']
                )
            )
            ->exec();
        }

        return true;
    }

}