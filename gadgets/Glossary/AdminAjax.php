<?php
/**
 * Glossary AJAX API
 *
 * @category   Ajax
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_AdminAjax extends Jaws_Gadget_HTML
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
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'Model', 'Term');
        $term = $model->GetTerm($id);
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
        $this->gadget->CheckPermission('AddTerm');
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'AdminModel', 'Term');

        $contents = jaws()->request->get(2, 'post', false);
        $id = $model->NewTerm($term, $fast_url, $contents);
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
        $this->gadget->CheckPermission('UpdateTerm');
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'AdminModel', 'Term');

        $contents = jaws()->request->get(3, 'post', false);
        $model->UpdateTerm($id, $term, $fast_url, $contents);
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
        $this->gadget->CheckPermission('DeleteTerm');
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'AdminModel', 'Term');
        $model->DeleteTerm($id);
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
        $text = jaws()->request->get(0, 'post', false);
        $gadget = $GLOBALS['app']->LoadGadget('Glossary', 'AdminHTML');
        return $gadget->gadget->ParseText($text);
    }

}