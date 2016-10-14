<?php
/**
 * Quotes - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Quotes
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
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
        $objORM->table('quotes');
        $objORM->select('id', 'title', 'quotation', 'updatetime');
        $objORM->where('published', true);
        $objORM->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('id')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $quotations = array();
        foreach ($result as $r) {
            $quotation = array();
            $quotation['title']   = $r['title'];
            $quotation['url']     = $this->gadget->urlMap('ViewQuote', array('id' => $r['id']));
            $quotation['image']   = 'gadgets/Quotes/Resources/images/logo.png';
            $quotation['snippet'] = $r['quotation'];
            $quotation['date']    = $date->ToISO($r['updatetime']);
            $quotations[] = $quotation;
        }

        return $quotations;
    }
}
