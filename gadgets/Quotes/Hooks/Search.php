<?php
/**
 * Quotes - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Quotes
 */
class Quotes_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget search fields
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
            'quotes' => array('title', 'quotation'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $classification = $this->gadget->action->load('Quotes')->getCurrentUserClassification();

        $objORM->table('quotes');
        $objORM->select('id', 'title', 'quotation', 'meta_keywords', 'meta_description', 'updated');
        $objORM->where('published', true);
        $objORM->and()->openWhere(
            'ftime',
            time(),
            '<='
        )->or()->closeWhere('ftime', 0);
        $objORM->and()->openWhere(
            'ttime',
            time(),
            '>'
        )->or()->closeWhere('ttime', 0);
        $objORM->and()->where('classification', $classification, '<=');
        $objORM->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('order')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $quotations = array();
        foreach ($result as $r) {
            $quotation = array();
            $quotation['title']   = $r['title'];
            $quotation['url']     = $this->gadget->urlMap('quote', array('id' => $r['id'], 'metaurl'=>$r['meta_keywords']));
            $quotation['image']   = 'gadgets/Quotes/Resources/images/logo.png';
            $quotation['snippet'] = $r['quotation'];
            $quotation['date']    = $date->ToISO($r['updated']);
            $quotations[] = $quotation;
        }

        return $quotations;
    }
}
