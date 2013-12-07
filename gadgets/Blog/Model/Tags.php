<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Tags extends Jaws_Gadget_Model
{
    /**
     * Generates a tag cloud
     *
     * @access  public
     * @return  mixed   An array on success and Jaws_Error in case of errors
     */
    function CreateTagCloud()
    {
        $table = Jaws_ORM::getInstance()->table('blog_entrycat');
        $table->select('count(category_id) as howmany:integer', 'name', 'fast_url', 'category_id:integer');
        $table->join('blog_category', 'category_id', 'id');
        $res = $table->groupBy('category_id', 'name', 'fast_url')->orderBy('name')->fetchAll();

        if (Jaws_Error::isError($res)) {
            return new Jaws_Error(_t('BLOG_ERROR_TAGCLOUD_CREATION_FAILED'));
        }

        return $res;
    }

}