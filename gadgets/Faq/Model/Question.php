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
class Faq_Model_Question extends Jaws_Gadget_Model
{
    /**
     * Get the list of questions
     *
     * @access  public
     * @param   int     $category   Just questions from this category(optional)
     * @param   bool    $justactive
     * @return  mixed   Returns an array of questions and Jaws_Error on error
     */
    function GetQuestions($category = null, $justactive = false)
    {
        $faqCategoryTable = Jaws_ORM::getInstance()->table('faq_category');
        $faqCategoryTable->select(
            'faq.id:integer', 'question', 'faq.fast_url', 'answer', 'faq.faq_position:integer',
            'faq_category.id as cat_id:integer', 'faq_category.category_position:integer', 'faq_category.category',
            'faq_category.fast_url as cat_fast_url', 'faq_category.description', 'faq.createtime',
            'faq.updatetime', 'faq.published:boolean'
        );
        $faqCategoryTable->join('faq', 'faq_category.id', 'faq.category', 'left');


        if ($category) {
            if (is_numeric($category)) {
                $faqCategoryTable->where('faq_category.id', $category);
            } else {
                $faqCategoryTable->where('faq_category.fast_url', $category);
            }
        }

        if ($justactive) {
            $faqCategoryTable->and()->where('published', true);
        }
        $result = $faqCategoryTable->orderBy('faq_category.category_position', 'faq.faq_position')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $aux = '';
        $pos = 0;
        $res = array();
        $date = Jaws_Date::getInstance();
        foreach ($result as $r) {
            if ($r['category'] != $aux) {
                $pos++;
                $res[$pos]['id']          = $r['cat_id'];
                $res[$pos]['category']    = $r['category'];
                $res[$pos]['fast_url']    = $r['cat_fast_url'];
                $res[$pos]['description'] = $r['description'];
                $res[$pos]['position']    = $r['category_position'];
                $aux = $r['category'];
            }

            if ($r['id'] != '') {
                $q = array();
                $q['id']         = $r['id'];
                $q['category']   = $r['cat_id'];
                $q['position']   = $r['faq_position'];
                $q['question']   = $r['question'];
                $q['fast_url']   = $r['fast_url'];
                $q['answer']     = $r['answer'];
                $q['active']     = $r['published'];
                $q['createtime'] = $date->ToISO($r['createtime']);
                $q['updatetime'] = $date->ToISO($r['updatetime']);
                $res[$pos]['questions'][] = $q;
            }
        }

        return $res;
    }

    /**
     * Get a question in specific
     *
     * @access  public
     * @param   string  $id   Number of the question
     * @return  mixed   An array with the properties of a question FAQ and Jaws_Error on error
     */
    function GetQuestion($id)
    {
        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $faqTable->select(
            'faq.id:integer', 'question', 'faq.fast_url', 'answer', 'faq.category as category_id:integer',
            'published:boolean', 'faq.faq_position:integer', 'faq_category.category', 'faq.createtime',
            'faq.updatetime');
        $faqTable->join('faq_category', 'faq.category', 'faq_category.id', 'left');

        if (is_numeric($id)) {
            $faqTable->where('faq.id', $id);
        } else {
            $faqTable->where('faq.fast_url', $id);
        }

        $row = $faqTable->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        return $row;
    }
}