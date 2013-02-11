<?php
/**
 * FAQ AJAX API
 *
 * @category   Ajax
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Faq_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Delete a category
     *
     * @access  public
     * @param   int     $id     Category ID
     * @return  array   Response array (notice or error)
     */
    function DeleteCategory($id)
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->_Model->DeleteCategory($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a question
     *
     * @access  public
     * @param   int     $id     Question ID
     * @return  array   Response array (notice or error)
     */
    function DeleteQuestion($id)
    {
        $this->gadget->CheckPermission('DeleteQuestion');
        $this->_Model->DeleteQuestion($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Move a question
     *
     * @access  public
     * @param   int     $id        Question ID
     * @param   string  $direction Direction (up/down)
     * @return  array   Response array (notice or error)
     */
    function MoveQuestion($id, $direction)
    {
        $this->_Model->MoveQuestion($direction, $id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Move a category
     *
     * @access  public
     * @param   int     $cat            Category ID
     * @param   int     $old_position   Old position of category
     * @param   int     $new_position   New position of category
     * @return  array   Response array (notice or error)
     */
    function MoveCategory($cat, $old_position, $new_position)
    {
        $result = $this->_Model->MoveCategory($cat, $old_position, $new_position);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_MOVED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Parse text
     *
     * @access  public
     * @return  string  Parsed text
     */
    function ParseText()
    {
        $request =& Jaws_Request::getInstance();
        $text = $request->get(0, 'post', false);

        $gadget = $GLOBALS['app']->LoadGadget('Faq', 'AdminHTML');
        return $gadget->gadget->ParseText($text, 'Faq');
    }

    /**
     * Rebuild the work area of a category
     *
     * @access  public
     * @param   int     $id        Category ID
     * @return  string  XHTML template content
     */
    function GetCategoryGrid($id)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Faq', 'AdminHTML');
        $datagrid = $gadget->DataGrid($id);

        if (!empty($datagrid)) {
            return $datagrid;
        }

        ///FIXME what's add_url suppose to be ?
        $noQuestions = "<span class=\"control-panel-message\">\n";
        $noQuestions.= "<a href=\"{$add_url}\">"._t('FAQ_START_ADD')."</a>"."\n";
        $noQuestions.= "</span>\n";
        $noQuestions.= "</div>\n";
        return $noQuestions;
    }

}