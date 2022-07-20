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
     * @param   string      $random    Order by random ?
     * @return  array       data
     */
    function list($filters = array(), $limit = false, $offset = null, $orderBy = 'q.ptime desc', $random = false)
    {
        $qTable = Jaws_ORM::getInstance()
            ->table('quotes as q')
            ->select(
                'q.id:integer', 'q.title', 'q.quotation', 'q.classification:integer',
                'q.ptime:integer', 'q.xtime:integer', 'q.meta_keywords', 'q.meta_description', 'q.published:boolean',
                'q.inserted:integer', 'q.updated:integer', 'cat.title as category_title', 'cat.id as category:integer'
            )->join('categories_references as cr', 'cr.reference', 'q.id')
            ->join('categories as cat', 'cat.id', 'cr.category')
            ->where('cat.gadget', $this->gadget->name)
            ->and()->where('cat.action', 'Quotes')
            ->and()->where(
                'cat.id',
                @$filters['category'],
                '=',
                empty($filters['category'])
            )->and()->where(
             'q.title',
                @$filters['term'],
                'like',
                empty($filters['term'])
            )->and()->where(
                'q.classification',
                is_array($filters['classification']) ? $filters['classification'][0] : $filters['classification'],
                is_array($filters['classification']) ? $filters['classification'][1] : '=',
                empty($filters['classification'])
            )->and()->where(
                'q.classification',
                @$filters['classification_is'],
                '=',
                empty($filters['classification_is'])
            )->and()->where(
                'q.published',
                (bool)@$filters['published'],
                '=',
                ($filters['published'] === '')
            )->and()->where(
                'q.ptime',
                @$filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'q.ptime',
                @$filters['to_date'],
                '<=',
                empty($filters['to_date'])
            );

        if (!empty($filters['ptime'])) {
            $qTable->and()->openWhere(
                'q.ptime',
                $filters['ptime'],
                '<=',
                empty($filters['ptime'])
            )->or()->closeWhere('ptime', 0);
        }
        if (!empty($filters['xtime'])) {
            $qTable->and()->openWhere(
                'q.xtime',
                $filters['xtime'],
                '>',
                empty($filters['xtime'])
            )->or()->closeWhere('xtime', 0);
        }

        if ($random) {
            $qTable->orderBy($qTable->random());
        } else {
            $qTable->orderBy($orderBy);
        }

        return $qTable->limit((int)$limit, $offset)->fetchAll();
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
        $qTable = Jaws_ORM::getInstance()
            ->table('quotes as q')
            ->select('count(q.id):integer')
            ->join('categories_references as cr', 'cr.reference', 'q.id')
            ->join('categories as cat', 'cat.id', 'cr.category')
            ->where('cat.gadget', $this->gadget->name)
            ->and()->where('cat.action', 'Quotes')
            ->and()->where(
                'cat.id',
                @$filters['category'],
                '=',
                empty($filters['category'])
            )->and()->where(
                'q.title',
                @$filters['term'],
                'like',
                empty($filters['term'])
            )->and()->where(
                'q.classification',
                is_array($filters['classification']) ? $filters['classification'][0] : $filters['classification'],
                is_array($filters['classification']) ? $filters['classification'][1] : '=',
                empty($filters['classification'])
            )->and()->where(
                'q.classification',
                @$filters['classification_is'],
                '=',
                empty($filters['classification_is'])
            )->and()->where(
                'q.published',
                (bool)@$filters['published'],
                '=',
                ($filters['published'] === '')
            )->and()->where(
                'q.ptime',
                @$filters['from_date'],
                '>=',
                empty($filters['from_date'])
            )->and()->where(
                'q.ptime',
                @$filters['to_date'],
                '<=',
                empty($filters['to_date'])
            );

        if (!empty($filters['ptime'])) {
            $qTable->and()->openWhere(
                'q.ptime',
                $filters['ptime'],
                '<=',
                empty($filters['ptime'])
            )->or()->closeWhere('ptime', 0);
        }
        if (!empty($filters['xtime'])) {
            $qTable->and()->openWhere(
                'q.xtime',
                $filters['xtime'],
                '>',
                empty($filters['xtime'])
            )->or()->closeWhere('xtime', 0);
        }


        return $qTable->fetchOne();
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
        return Jaws_ORM::getInstance()
            ->table('quotes as q')
            ->select(
                'q.id:integer', 'q.title', 'q.quotation', 'q.classification:integer',
                'q.ptime:integer', 'q.xtime:integer', 'q.meta_keywords', 'q.meta_description', 'q.published:boolean',
                'q.inserted:integer', 'q.updated:integer', 'cat.title as category_title', 'cat.id as category:integer'
            )->join('categories_references as cr', 'cr.reference', 'q.id')
            ->join('categories as cat', 'cat.id', 'cr.category')
            ->where('cat.gadget', $this->gadget->name)
            ->and()->where('cat.action', 'Quotes')
            ->and()->where('q.id', $id)
            ->fetchRow();
    }
}