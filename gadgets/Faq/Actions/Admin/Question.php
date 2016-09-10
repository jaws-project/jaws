<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Actions_Admin_Question extends Faq_Actions_Admin_Default
{
    /**
     * Displays faq list of questions(admin mode)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Questions()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Questions.html');
        $tpl->SetBlock('Questions');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Questions'));

        //Category filter
        $bCategory =& Piwi::CreateWidget('Combo', 'category_filter');
        $bCategory->setStyle('min-width:150px;');
        $bCategory->AddEvent(ON_CHANGE, "getQuestionsDataGrid('questions_datagrid', 0, true)");
        $bCategory->AddOption('&nbsp;', 0);
        $model = $this->gadget->model->load('Category');
        $categories = $model->GetCategories();
        foreach($categories as $category) {
            $bCategory->AddOption($category['category'], $category['id']);
        }
        $tpl->SetVariable('category_filter', $bCategory->Get());
        $tpl->SetVariable('lbl_category', _t('FAQ_CATEGORY'));

        $tpl->SetVariable('grid', $this->QuestionsDatagrid());
        $tpl->SetVariable('question_ui', $this->QuestionUI());
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "javascript:saveQuestion();");
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript:stopAction();");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('incompleteQuestionFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmQuestionDelete',    _t('FAQ_CONFIRM_DELETE_QUESTION'));
        $tpl->SetVariable('legend_title',             _t('FAQ_ADD_QUESTION'));
        $tpl->SetVariable('addQuestion_title',        _t('FAQ_ADD_QUESTION'));
        $tpl->SetVariable('editQuestion_title',       _t('FAQ_EDIT_QUESTION'));

        $tpl->ParseBlock('Questions');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given question
     *
     * @access  public
     * @return  string XHTML template content
     */
    function QuestionUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Questions.html');
        $tpl->SetBlock('QuestionInfo');

        //question
        $questionEntry =& Piwi::CreateWidget('Entry', 'question', '');
        $tpl->SetVariable('lbl_question', _t('FAQ_QUESTION'));
        $tpl->SetVariable('question', $questionEntry->Get());

        //answer
        $answer =& $GLOBALS['app']->LoadEditor('Faq', 'answer', '', false, _t('FAQ_ANSWER'));
        $answer->TextArea->SetStyle('width: 100%;');
        $answer->TextArea->SetRows(8);
        $answer->setID('answer');
        $tpl->SetVariable('lbl_answer', _t('FAQ_ANSWER'));
        $tpl->SetVariable('answer', $answer->Get());

        //category
        $category_combo =& Piwi::CreateWidget('Combo', 'category');
        $category_combo->SetID('category');
        $model = $this->gadget->model->load('Category');
        $categories = $model->GetCategories();
        foreach($categories as $category) {
            $category_combo->AddOption($category['category'], $category['id']);
        }
        $category_combo->setDefault($categories[0]['id']);
        $tpl->SetVariable('lbl_category', _t('FAQ_CATEGORY'));
        $tpl->SetVariable('category', $category_combo->Get());

        //fast url
        $fast_url =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fast_url->SetID('fast_url');
        $tpl->SetVariable('lbl_fast_url', _t('FAQ_FASTURL'));
        $tpl->SetVariable('fast_url', $fast_url->Get());

        // meta_keywords
        $entry =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $tpl->SetVariable('lbl_meta_keywords', _t('GLOBAL_META_KEYWORDS'));
        $tpl->SetVariable('meta_keywords', $entry->Get());

        // meta_description
        $entry =& Piwi::CreateWidget('Entry', 'meta_description', '');
        $tpl->SetVariable('lbl_meta_description', _t('GLOBAL_META_DESCRIPTION'));
        $tpl->SetVariable('meta_description', $entry->Get());

        //published
        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->AddOption(_t('GLOBAL_NO'),  0);
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->SetDefault('1');
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $tpl->ParseBlock('QuestionInfo');
        return $tpl->Get();
    }

    /**
     * Build the datagrid of questions
     *
     * @access  public
     * @return  string  XHTML template of Datagrid
     */
    function QuestionsDatagrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('faq');
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('questions_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(18);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 80px; white-space:nowrap;');
        $grid->AddColumn($column2);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Prepares the data (an array) of questions
     *
     * @access  public
     * @return  array   Data
     */
    function GetQuestions()
    {
        $post = jaws()->request->fetch(array('offset', 'category'), 'post');
        $filters = array('category' => $post['category']);
        $model = $this->gadget->model->load('Question');
        $questions = $model->GetAllQuestions($filters, 15, $post['offset']);
        if (Jaws_Error::IsError($questions)) {
            return array();
        }

        $newData = array();
        foreach($questions as $question) {
            $questionData = array();
            $questionData['question'] = $question['question'];
            $actions = '';
            if ($this->gadget->GetPermission('EditQuestion')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript:editQuestion(this, '".$question['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('FAQ_MOVEUP'),
                    "javascript:moveQuestion(" . $question['category'] . "," . $question['id'] . "," . $question['faq_position'] . ", -1);",
                    STOCK_UP);
                $actions .= $link->Get() . '&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('FAQ_MOVEDOWN'),
                    "javascript:moveQuestion(" . $question['category'] . "," . $question['id'] . "," . $question['faq_position'] . ", 1);",
                    STOCK_DOWN);
                $actions .= $link->Get() . '&nbsp;';
            }
            if ($this->gadget->GetPermission('DeleteQuestion')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript:deleteQuestion(this, '" . $question['id'] . "');",
                    STOCK_DELETE);
                $actions .= $link->Get() . '&nbsp;';
            }
            $questionData['actions'] = $actions;
            $newData[] = $questionData;
        }
        return $newData;
    }

    /**
     * Get questions count
     *
     * @access  public
     * @return  int     Total of logs
     */
    function GetQuestionsCount()
    {
        $category = jaws()->request->fetch('category', 'post');
        $model = $this->gadget->model->loadAdmin('Question');
        return $model->GetQuestionsCount($category);
    }


    /**
     * Get a questions info
     *
     * @access  public
     * @return  array     Question info
     */
    function GetQuestion()
    {
        $this->gadget->CheckPermission('EditQuestion');
        $id = jaws()->request->fetch('id', 'post');
        $model = $this->gadget->model->load('Question');
        return $model->GetQuestion($id);
    }

    /**
     * Insert new question
     *
     * @access  public
     * @return  boolean
     */
    function InsertQuestion()
    {
        $this->gadget->CheckPermission('AddQuestion');
        $data = jaws()->request->fetch('data:array', 'post');
        $model = $this->gadget->model->loadAdmin('Question');
        $res = $model->InsertQuestion($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_ERROR_QUESTION_NOT_ADDED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_QUESTION_ADDED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Update a question
     *
     * @access  public
     * @return  boolean
     */
    function UpdateQuestion()
    {
        $this->gadget->CheckPermission('EditQuestion');
        $post = jaws()->request->fetch(array('id', 'data:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Question');
        $res = $model->UpdateQuestion($post['id'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_ERROR_QUESTION_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_QUESTION_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete question
     *
     * @access  public
     */
    function DeleteQuestion()
    {
        $this->gadget->CheckPermission('DeleteQuestion');
        $model = $this->gadget->model->loadAdmin('Question');

        $id = (int)jaws()->request->fetch('id', 'post');
        $res = $model->DeleteQuestion($id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_QUESTION_DELETED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Move a question
     *
     * @access   public
     * @return   array  Response array (notice or error)
     */
    function MoveQuestion()
    {
        $post = jaws()->request->fetch(array('category', 'id', 'position', 'direction'), 'post');
        $model = $this->gadget->model->loadAdmin('Question');
        $result = $model->MoveQuestion($post['category'], $post['id'], $post['position'], $post['direction']);
        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('FAQ_QUESTION_MOVED'), RESPONSE_NOTICE);
        }
    }
}