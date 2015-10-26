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
                    array('[term]', '[description]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql  Prepared search (WHERE) SQL
     * @return  mixed   An array of entries that matches a certain pattern or False on error
     */
    function Execute($pSql = '')
    {
        $sql = '
            SELECT
                [id], [term], [description], [createtime]
            FROM [[glossary]]
            ';

        $sql .= ' WHERE ' . $pSql;
        $sql .= " ORDER BY [createtime] desc";

        $result = Jaws_DB::getInstance()->queryAll($sql);
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