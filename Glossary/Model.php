<?php
/**
 * Glossary Gadget
 *
 * @category   GadgetModel
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Model extends Jaws_Gadget_Model
{
    /**
     * Get a term
     *
     * @access  public
     * @param   int     $id     Term ID
     * @return  mixed   Returns the properties of a term and Jaws_Error on error
     */
    function GetTerm($id)
    {
        $params = array();
        $params['id'] = $id;
        $sql = "
            SELECT
                [id], [user_id], [term], [fast_url], [description], [createtime], [updatetime]
            FROM [[glossary]]
            WHERE ";
        $sql .= is_numeric($id) ? '[id] = {id}' : '[fast_url] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a term
     *
     * @access  public
     * @param   string  $term   Term
     * @return  mixed   Returns the properties of a term and Jaws_Error on error
     */
    function GetTermByTerm($term)
    {
        $sql = "
            SELECT
                [id], [user_id], [term], [fast_url], [description], [createtime], [updatetime]
            FROM [[glossary]]
            WHERE ";
        $sql.= $GLOBALS['db']->dbc->datatype->matchPattern(array(1 => '%', $term, '%'), 'ILIKE', '[term]');

        $result = $GLOBALS['db']->queryRow($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a random term
     *
     * @access  public
     * @return  mixed   Returns the properties of a term and Jaws_Error on error
     */
    function GetRandomTerm()
    {
        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $rand = $GLOBALS['db']->dbc->function->random();
        $sql = '
            SELECT
                [id], [user_id], [term], [fast_url], [description], [updatetime]
            FROM [[glossary]]
            ORDER BY ' . $rand;

        $result = $GLOBALS['db']->setLimit('1');
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $result = $GLOBALS['db']->queryRow($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get a list of all the terms
     *
     * @access  public
     * @return  mixed   Returns an array with all the terms or Jaws_Error on error
     */
    function GetTerms()
    {
        $sql = "
            SELECT
                [id], [user_id], [term], [fast_url], [description], [updatetime]
            FROM [[glossary]]
            ORDER BY [term]";

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

}