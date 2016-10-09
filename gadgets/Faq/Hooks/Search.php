<?php
/**
 * Faq - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array search fields array
     */
    function GetOptions() {
        return array(
            'faq' => array('question', 'answer'),
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
        $objORM->table('faq');
        $objORM->select('category', 'question', 'answer', 'faq_position', 'updatetime');
        $objORM->where('published', true);
        $objORM->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('createtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $questions  = array();
        $date = Jaws_Date::getInstance();
        foreach ($result as $r) {
            $question = array();
            $question['title']   = $r['question'];
            $question['url']     = $this->gadget->urlMap('ViewCategory', array('id' => $r['category'])).
                '#Question'.$r['faq_position'];
            $question['image']   = 'gadgets/Faq/Resources/images/logo.png';
            $question['snippet'] = $r['answer'];
            $question['date']    = $date->ToISO($r['updatetime']);
            $questions[] = $question;
        }

        return $questions;
    }

}