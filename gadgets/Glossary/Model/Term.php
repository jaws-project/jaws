<?php
/**
 * Glossary Gadget
 *
 * @category   GadgetModel
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Model_Term extends Jaws_Gadget_Model
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
        $exp1 = is_numeric($id) ? 'id' : 'fast_url';
        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->select(
            'id:integer', 'user_id:integer', 'term', 'fast_url', 'description', 'createtime', 'updatetime'
        )->where($exp1, $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
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
        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $glossaryTable->select(
            'id', 'user_id', 'term', 'fast_url',
            'description', 'createtime', 'updatetime'
        );
        return $glossaryTable->where('term', $term, 'like')->fetchRow();
    }

    /**
     * Get a random term
     *
     * @access  public
     * @return  mixed   Returns the properties of a term and Jaws_Error on error
     */
    function GetRandomTerm()
    {
        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->select(
            'id:integer', 'user_id:integer', 'term', 'fast_url', 'description', 'updatetime'
        )->orderBy($glossaryTable->random())->limit(1)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
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
        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->select(
            'id:integer', 'user_id:integer', 'term', 'fast_url', 'description', 'updatetime'
        )->orderBy('term')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}