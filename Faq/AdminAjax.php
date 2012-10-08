<?php
/**
 * FAQ AJAX API
 *
 * @category   Ajax
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FaqAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     * @param   Jaws_Model  $model  Jaws_Model reference
     */
    function FaqAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Ugly but fast hack.. it sorts an array by key
     *
     * @access  public
     * @param   array   $array  Input array
     * @return  array   Sorted array
     */
    function KSort($array)
    {
        ksort($array);
        return $array;
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
        $this->CheckSession('Faq', 'ManageCategories');
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
        $this->CheckSession('Faq', 'DeleteQuestion');
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
     * @param   int     $id        Category ID
     * @param   string  $direction Direction (up/down)
     * @return  array   Response (notice or error)
     */
    function MoveCategory($id, $direction)
    {
        $this->_Model->MoveCategory($direction, $id);
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
        $gadget = $GLOBALS['app']->LoadGadget('Faq', 'AdminHTML');
        return $gadget->ParseText($text, 'Faq');
    }

    /**
     * Fix positions..
     *
     * @access  public
     * @param   array   $categories     Array with information and positions of each category
     * @return  array   Response array (notice or error)
     */
    function FixPositions($categories)
    {
        $origCategories = $this->_Model->GetCategories();
        if (Jaws_Error::IsError($origCategories)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
            return $GLOBALS['app']->Session->PopLastResponse();
        }

        $foundOne = false;

        foreach ($origCategories as $category) {
            if (isset($categories[$category['id']]) && is_array($categories[$category['id']])) {
                if (isset($categories[$category['id']]['pos'])) {
                    $newPosition   = $categories[$category['id']]['pos'];
                    $origPosition  = $category['category_position'];

                    if ($newPosition != $origPosition) {
                        $foundOne = true;
                        $this->_Model->FixCategoryPosition($category['id'], $newPosition);
                    }
                }
            }
        }

        if (!$foundOne) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_MOVED'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
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