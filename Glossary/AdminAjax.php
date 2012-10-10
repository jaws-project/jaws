<?php
/**
 * Glossary AJAX API
 *
 * @category   Ajax
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class GlossaryAdminAjax extends Jaws_Ajax
{
    /**
     * Get a term
     *
     * @access  public
     * @param   int    $id Term ID
     * @return  mixed  Term data or false on error
     */
    function GetTerm($id)
    {
        $term = $this->_Model->GetTerm($id);
        if (Jaws_Error::IsError($term)) {
            return false;
        }

        return $term;
    }

    /**
     * Create a new term
     *
     * @access  public
     * @param   string  $term
     * @param   string  $fast_url
     * @param   string  $contents    Term description
     * @return  array   Response array (notice or error)
     */
    function NewTerm($term, $fast_url, $contents)
    {
        $this->CheckSession('Glossary', 'AddTerm');
        $id = $this->_Model->NewTerm($term, $fast_url, $contents);
        $response = $GLOBALS['app']->Session->PopLastResponse();
        $response['id'] = $id;
        return $response;
    }

    /**
     * Update a term
     *
     * @access  public
     * @param   int     $id         Term ID
     * @param   string  $term       Term
     * @param   string  $fast_url
     * @param   string  $contents   Term description
     * @return  array   Response array (notice or error)
     */
    function UpdateTerm($id, $term, $fast_url, $contents)
    {
        $this->CheckSession('Glossary', 'UpdateTerm');
        $this->_Model->UpdateTerm($id, $term, $fast_url, $contents);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a term
     *
     * @access  public
     * @param   int     $id  Term ID
     * @return  array   Response array (notice or error)
     */
    function DeleteTerm($id)
    {
        $this->CheckSession('Glossary', 'DeleteTerm');
        $this->_Model->DeleteTerm($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Parse text
     *
     * @access  public
     * @param   string  $text    Input text (not parsed)
     * @return  string  Parsed text
     */
    function ParseText($text)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Glossary', 'AdminHTML');
        return $gadget->ParseText($text, 'Glossary');
    }

}