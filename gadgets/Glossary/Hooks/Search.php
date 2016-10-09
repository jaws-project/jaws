<?php
/**
 * Glossary - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   search fields array
     */
    function GetOptions() {
        return array(
            'glossary' => array('term', 'description'),
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
        $objORM->table('glossary');
        $objORM->select('id', 'term', 'description', 'createtime');
        $objORM->loadWhere('search.terms');
        $result = $objORM->orderBy('createtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $entries = array();
        foreach ($result as $r) {
            $entry = array();
            $entry['title']   = $r['term'];
            $entry['url']     = $this->gadget->urlMap('ViewTerm', array('term' => $r['id']));
            $entry['image']   = 'gadgets/Glossary/Resources/images/logo.png';
            $entry['snippet'] = $r['description'];
            $entry['date']    = $date->ToISO($r['createtime']);
            $stamp = str_replace(array('-', ':', ' '), '', $r['createtime']);
            $entries[$stamp] = $entry;
        }
        return $entries;
    }

}