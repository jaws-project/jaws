<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Categories extends Jaws_Gadget_Model
{
    /**
     * Get categories
     *
     * @access  public
     * @param   bool    $onlyAccessible     return only accessible category
     * @return mixed A list of categories and Jaws_Error on error
     */
    function GetCategories($onlyAccessible = true)
    {
        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $catTable->select('id', 'name', 'fast_url', 'description', 'createtime', 'updatetime');
        $catTable->orderBy('name');
        $categories = $catTable->fetchAll();

        // Check dynamic ACL
        if ($onlyAccessible) {
            foreach ($categories as $key => $category) {
                if (!$this->gadget->GetPermission('CategoryAccess', $category['id'])) {
                    unset($categories[$key]);
                }
            }
        }

        return $categories;
    }

    /**
     * Gets a category data
     *
     * @access  public
     * @param   int     $id  Category ID
     * @return  mixed   Array of category data or Jaws_Error
     */
    function GetCategory($id)
    {
        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $catTable->select(
            'id', 'name', 'fast_url', 'description',
            'meta_keywords', 'meta_description', 'createtime', 'updatetime'
        );

        if (is_numeric($id)) {
            $catTable->where('id', $id);
        } else {
            $catTable->where('fast_url', $id);
        }

        $cat = $catTable->fetchRow();
        if(!Jaws_Error::IsError($cat)) {
            $image_path = $this->GetCategoryLogoPath($id);
            if(file_exists($image_path)) {
                $cat['image_url'] = $this->GetCategoryLogoURL($id);
            } else {
                $cat['image_url'] = $GLOBALS['app']->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
            }
        }

        return $cat;
    }

    /**
     * Get a category
     *
     * @access  public
     * @param   string  $name   category name
     * @return  mixed   A category array or Jaws_Error
     */
    function GetCategoryByName($name)
    {
        $name = Jaws_UTF8::strtolower($name);
        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $catTable->select('id:integer', 'name', 'description', 'fast_url', 'createtime', 'updatetime');
        $result = $catTable->where($catTable->lower('name'), $name)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_CATEGORY'));
        }

        return $result;
    }

    /**
     * Get categories in a given entry
     *
     * @access  public
     * @param   int     $post_id  Post ID
     * @return  array   Returns an array with the categories in a given post
     */
    function GetCategoriesInEntry($post_id)
    {
        $catTable = Jaws_ORM::getInstance()->table('blog_entrycat');
        $catTable->select('category_id as id:integer', 'name', 'fast_url');
        $categories = $catTable->join('blog_category', 'category_id', 'id')->where('entry_id', $post_id)->fetchAll();
        if (Jaws_Error::isError($categories)) {
            return array();
        }

        return $categories;
    }

    /**
     * Get categories in entries
     *
     * @access  public
     * @param   int     $ids Array with post id's
     * @return  array   Returns an array with the categories in a given post
     */
    function GetCategoriesInEntries($ids)
    {
        $categories = array();
        if (is_array($ids) && count($ids) > 0) {

            $catTable = Jaws_ORM::getInstance()->table('blog_entrycat');
            $catTable->select('category_id as id:integer', 'entry_id:integer', 'name', 'fast_url');
            $catTable->join('blog_category', 'category_id', 'id')->where('entry_id', $ids, 'in');
            $categories = $catTable->fetchAll();

            if (Jaws_Error::isError($categories)) {
                return array();
            }
        }

        return $categories;
    }

    /**
     * Get number of category's pages
     *
     * @access  public
     * @param   int     $category   category Id
     * @return  int number of pages
     */
    function GetCategoryNumberOfPages($category)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('count(blog.id)');
        $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id', 'left');
        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        if (empty($category)) {
            $blogTable->and()->where('blog_entrycat.category_id', null, 'is null');
        } else {
            $blogTable->and()->where('blog_entrycat.category_id', (int)$category);
        }

        $howmany = $blogTable->fetchOne();
        return Jaws_Error::IsError($howmany)? 0 : $howmany;
    }

    /**
     * Get category logo path
     *
     * @access  public
     * @param   int     $id     Category id
     * @return  bool True or error
     */
    function GetCategoryLogoPath($id)
    {
        return JAWS_DATA . "blog/categories/$id.png";
    }

    /**
     * Get category logo path
     *
     * @access  public
     * @param   int     $id         Category id
     * @param   bool    $rel_url    Relative URL
     * @return  bool True or error
     */
    function GetCategoryLogoURL($id, $rel_url = true)
    {
        return $GLOBALS['app']->getDataURL("blog/categories/$id.png", $rel_url);
    }
}