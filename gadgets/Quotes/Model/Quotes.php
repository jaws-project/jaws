<?php
/**
 * Quotes Model
 *
 * @category   GadgetModel
 * @package    Quotes
 */
class Quotes_Model_Quotes extends Jaws_Gadget_Model
{
    /**
     * Get list of quotes item
     *
     * @access  public
     * @param   array       $filters
     * @param   bool|int    $limit     Count of quotes to be returned
     * @param   int         $offset    Offset of data array
     * @param   string      $orderBy   Order by
     * @return  array       data
     */
    function list($filters = array(), $limit = false, $offset = null, $orderBy = 'order desc')
    {
        return Jaws_ORM::getInstance()
            ->table('quotes')
            ->select(
                'id:integer', 'title', 'quotation', 'classification:integer', 'order:integer',
                'ftime:integer', 'ttime:integer', 'meta_keywords', 'meta_description', 'published:boolean',
                'inserted:integer', 'updated:integer'
            )->where(
                'title',
                @$filters['term'],
                'like',
                empty($filters['term'])
            )->and()->where(
                'classification',
                @$filters['classification'],
                '=',
                empty($filters['classification'])
            )->and()->where(
                'published',
                @$filters['published'],
                '=',
                !isset($filters['published'])
            )->orderBy($orderBy)
            ->limit((int)$limit, $offset)
            ->fetchAll();
    }

    /**
     * Get quotes count
     *
     * @access  public
     * @param   array       $filters
     * @return  int         quotes count
     */
    function count($filters)
    {
        return Jaws_ORM::getInstance()
            ->table('quotes')
            ->select('count(id):integer')
            ->where(
                'title',
                @$filters['term'],
                'like',
                empty($filters['term'])
            )->and()->where(
                'classification',
                @$filters['classification'],
                '=',
                empty($filters['classification'])
            )->and()->where(
                'published',
                @$filters['published'],
                '=',
                !isset($filters['published'])
            )->fetchOne();
    }

    /**
     * Get a quote info
     *
     * @access  public
     * @param   int         $id    Quote id
     * @return  array       data
     */
    function get(int $id)
    {
        $quote = Jaws_ORM::getInstance()
            ->table('quotes')
            ->select(
                'id:integer', 'title', 'quotation', 'classification:integer', 'order:integer',
                'ftime:integer', 'ttime:integer', 'meta_keywords', 'meta_description', 'published:boolean',
                'inserted:integer', 'updated:integer'
            )->where('id', $id)
            ->fetchRow();
        if (Jaws_Error::IsError($quote)) {
            return $quote;
        }

        // quote categories
        $quote['category'] = array();
        if (!empty($quote)) {
            $category = Jaws_ORM::getInstance()->table('categories')
                ->select('categories.id:integer')
                ->where('gadget', $this->gadget->name)
                ->and()->where('action', 'Quotes')
                ->join('categories_references as r', 'r.category', 'categories.id')
                ->fetchOne();
            if (!Jaws_Error::IsError($category)) {
                $quote['category'] = $category;
            }
        }

        return $quote;
    }
}