<?php
/**
 * Faq Gadget
 *
 * @category   GadgetModel
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Model_Category extends Jaws_Gadget_Model
{

    /**
     * Get categories
     *
     * @access  public
     * @return  mixed    An array with the categories ordered by position or Jaws_Error on failure
     */
    function GetCategories()
    {
        $table = Jaws_ORM::getInstance()->table('faq_category');
        $table->select( 'id:integer', 'category', 'fast_url', 'description', 'category_position:integer', 'updatetime');
        $result = $table->orderBy('category_position asc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get category
     *
     * @access  public
     * @param   int     $id     Category ID
     * @return  mixed   Array an array with the category info or Jaws_Error on failure
     */
    function GetCategory($id)
    {
        $table = Jaws_ORM::getInstance()->table('faq_category');
        $table->select( 'id:integer', 'category', 'fast_url', 'description', 'category_position:integer', 'updatetime');
        $row = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_DOES_NOT_EXISTS'));
    }
}