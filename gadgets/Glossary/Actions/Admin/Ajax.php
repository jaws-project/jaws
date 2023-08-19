<?php
/**
 * Glossary AJAX API
 *
 * @category   Ajax
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get a term
     *
     * @access   public
     * @internal param  int     $id Term    ID
     * @return   mixed  Term data or false on error
     */
    function GetTerm()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Term');
        $term = $model->GetTerm($id);
        if (Jaws_Error::IsError($term)) {
            return false;
        }

        return $term;
    }

    /**
     * Create a new term
     *
     * @access   public
     * @internal param  string  $term
     * @internal param  string  $fast_url
     * @internal param  string  $contents   Term description
     * @return   array  Response array (notice or error)
     */
    function NewTerm()
    {
        $this->gadget->CheckPermission('AddTerm');
        @list($term, $fast_url, $contents) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Term');

        $contents = $this->gadget->request->fetch(2, 'post', false, array('filter' => 'strip_crlf'));
        $id = $model->NewTerm($term, $fast_url, $contents);
        $response = $this->gadget->session->pop();
        $response['id'] = $id;
        return $response;
    }

    /**
     * Update a term
     *
     * @access   public
     * @internal param  int     $id         Term ID
     * @internal param  string  $term       Term
     * @internal param  string  $fast_url
     * @internal param  string  $contents   Term description
     * @return   array  Response array (notice or error)
     */
    function UpdateTerm()
    {
        $this->gadget->CheckPermission('UpdateTerm');
        @list($id, $term, $fast_url, $contents) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Term');

        $contents = $this->gadget->request->fetch(3, 'post', false, array('filter' => 'strip_crlf'));
        $model->UpdateTerm($id, $term, $fast_url, $contents);
        return $this->gadget->session->pop();
    }

    /**
     * Delete a term
     *
     * @access   public
     * @internal param  int     $id     Term ID
     * @return   array  Response array (notice or error)
     */
    function DeleteTerm()
    {
        $this->gadget->CheckPermission('DeleteTerm');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Term');
        $model->DeleteTerm($id);
        return $this->gadget->session->pop();
    }

    /**
     * Parse text
     *
     * @access   public
     * @internal param  string  $text   Input text (not parsed)
     * @return   string Parsed text
     */
    function ParseText()
    {
        $text = $this->gadget->request->fetch(0, 'post', false, array('filter' => 'strip_crlf'));
        return $this->gadget->plugin->parseAdmin($text);
    }

}