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
class Faq_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Delete a category
     *
     * @access   public
     * @internal param  int  $id  Category ID
     * @return   array  Response array (notice or error)
     */
    function DeleteCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Category');
        $model->DeleteCategory($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a question
     *
     * @access   public
     * @internal param  int     $id    Question ID
     * @return   array  Response array (notice or error)
     */
    function DeleteQuestion()
    {
        $this->gadget->CheckPermission('DeleteQuestion');
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Question');
        $model->DeleteQuestion($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Move a question
     *
     * @access   public
     * @internal param  int     $cat        Category ID
     * @internal param  int     $id         Question ID
     * @internal param  int     $position   Position of question
     * @internal param  string  $direction  Direction (+1/-1)
     * @return   array  Response array (notice or error)
     */
    function MoveQuestion()
    {
        @list($cat, $id, $position, $direction) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Question');
        $result = $model->MoveQuestion($cat, $id, $position, $direction);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_QUESTION_MOVED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Move a category
     *
     * @access   public
     * @internal param  int     $cat            Category ID
     * @internal param  int     $old_position   Old position of category
     * @internal param  int     $new_position   New position of category
     * @return   array  Response array (notice or error)
     */
    function MoveCategory()
    {
        @list($cat, $old_position, $new_position) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Category');
        $result = $model->MoveCategory($cat, $old_position, $new_position);
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
        $text = jaws()->request->fetch(0, 'post', false);

        $gadget = $this->gadget->action->loadAdmin('Question');
        return $gadget->gadget->ParseText($text);
    }

    /**
     * Rebuild the work area of a category
     *
     * @access   public
     * @internal param  int     $id     Category ID
     * @return   string XHTML template content
     */
    function GetCategoryGrid()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Question');
        $datagrid = $gadget->DataGrid($id);

        if (!empty($datagrid)) {
            return $datagrid;
        }

        $add_url = BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion&amp;category='.$id;
        $noQuestions = "<span class=\"control-panel-message\">\n";
        $noQuestions.= "<a href=\"{$add_url}\">"._t('FAQ_START_ADD')."</a>"."\n";
        $noQuestions.= "</span>\n";
        $noQuestions.= "</div>\n";
        return $noQuestions;
    }

}