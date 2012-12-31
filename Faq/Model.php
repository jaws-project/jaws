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
class Faq_Model extends Jaws_Gadget_Model
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
        $sql = '
            SELECT
                [[faq]].[id],
                [question],
                [[faq]].[fast_url],
                [answer],
                [[faq]].[faq_position],
                [[faq_category]].[id] AS cat_id,
                [[faq_category]].[category_position],
                [[faq_category]].[category],
                [[faq_category]].[fast_url] AS cat_fast_url,
                [[faq_category]].[description],
                [[faq]].[createtime],
                [[faq]].[updatetime],
                [[faq]].[published]
            FROM [[faq_category]]
            LEFT JOIN [[faq]] ON [[faq_category]].[id] = [[faq]].[category]';
        if ($category) {
            if (is_numeric($category)) {
                $sql .= '
                    WHERE [[faq_category]].[id] = {category} ';
            } else {
                $sql .= '
                    WHERE [[faq_category]].[fast_url] = {category} ';
            }
        }

        if ($justactive) {
            $sql .= stristr($sql, 'WHERE') ? ' AND ' : ' WHERE  ';
            $sql .= '[published] = {active} ';
        }

        $sql .= '
            ORDER BY [[faq_category]].[category_position], [[faq]].[faq_position]';

        $params             = array();
        $params['category'] = $category;
        $params['active']   = true;

        $types = array('integer', 'text', 'text', 'text', 'integer', 'integer',
                       'integer', 'text', 'text', 'text', 'timestamp', 'timestamp', 'boolean');

        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $aux = '';
        $pos = 0;
        $res = array();
        $date = $GLOBALS['app']->loadDate();
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
        $sql = '
            SELECT
                [[faq]].[id],
                [question],
                [[faq]].[fast_url],
                [answer],
                [[faq]].[category] AS category_id,
                [published],
                [[faq]].[faq_position],
                [[faq_category]].[category],
                [[faq]].[createtime],
                [[faq]].[updatetime]
            FROM [[faq]]
            LEFT JOIN [[faq_category]] ON [[faq]].[category] = [[faq_category]].[id]';
        if (is_numeric($id)) {
            $sql .= '
                WHERE [[faq]].[id] = {id}';
        } else {
            $sql .= '
                WHERE [[faq]].[fast_url] = {id}';
        }

        $types = array('integer', 'text', 'text', 'text', 'integer', 'boolean',
                       'integer', 'text', 'timestamp', 'timestamp');

        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id), $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        return $row;
    }

    /**
     * Get categories
     *
     * @access  public
     * @return  mixed    An array with the categories ordered by position or Jaws_Error on failure
     */
    function GetCategories()
    {
        $sql = '
            SELECT
                [id],
                [category],
                [fast_url],
                [description],
                [category_position],
                [updatetime]
            FROM [[faq_category]]
            ORDER BY [category_position] ASC';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
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
        $sql = '
            SELECT
                [id],
                [category],
                [fast_url],
                [description],
                [category_position],
                [updatetime]
            FROM [[faq_category]]
            WHERE [id] = {category}';

        $row = $GLOBALS['db']->queryRow($sql, array('category' => $id));
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_DOES_NOT_EXISTS'));
    }
}