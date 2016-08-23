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
        $tpl->SetVariable('editQuestion_title',       _t('FAQ_ADD_QUESTION'));

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
        $answerText =& Piwi::CreateWidget('TextArea', 'answer','');
        $answerText->SetRows(8);
        $tpl->SetVariable('lbl_answer', _t('FAQ_ANSWER'));
        $tpl->SetVariable('answer', $answerText->Get());

        //category
        $category_combo =& Piwi::CreateWidget('Combo', 'category');
        $category_combo->SetID('category');
        $model = $this->gadget->model->load('Category');
        $categories = $model->GetCategories();
        foreach($categories as $category) {
            $category_combo->AddOption($category['category'], $category['id']);
        }
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
        $column2->SetStyle('width: 60px; white-space:nowrap;');
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
     * Displays faq list of questions(admin mode)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function QuestionsOLD()
    {
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/mootools/more.js');
        $category = jaws()->request->fetch('category', 'get');

        $manageTpl = $this->gadget->template->loadAdmin('Faq.html');
        $manageTpl->SetBlock('Faq');
        $manageTpl->SetVariable('base_script', BASE_SCRIPT);
        $manageTpl->SetVariable('menubar', $this->MenuBar(''));

        $qModel = $this->gadget->model->load('Question');
        $cModel = $this->gadget->model->load('Category');

        $catCombo =& Piwi::CreateWidget('Combo', 'category');
        $cats = $cModel->GetCategories();
        $catCombo->AddOption(_t('FAQ_ALL_CATEGORIES'), '*');
        foreach ($cats as $c) {
            $catCombo->AddOption($c['category_position'].'. '.$c['category'], $c['id']);
        }

        if (isset($category)) {
            $catCombo->SetDefault($category);
        } else {
            $catCombo->SetDefault('*');
        }

        $catCombo->AddEvent(ON_CHANGE, 'javascript:showCategory(this.form);');
        $manageTpl->SetVariable('category', _t('FAQ_CATEGORY'));
        $manageTpl->SetVariable('category_combo', $catCombo->Get());

        ///OK.. build the complete work area..
        $tpl = $this->gadget->template->loadAdmin('Faq.html');
        $tpl->SetBlock('ManageQuestions');

        $questions = $qModel->GetQuestions();
        $i = 0;

        if (is_array($questions) && count($questions) > 0) {
            foreach ($questions as $cat) {
                $tpl->SetBlock('ManageQuestions/category');
                $tpl->SetVariable('cat_id', $cat['id']);
                $tpl->SetVariable('position', $cat['position']);
                $tpl->SetVariable('i', $i);
                if (isset($category)) {
                    if ($category == $cat['id']) {
                        $tpl->SetVariable('style', 'display: block;');
                    } else {
                        $tpl->SetVariable('style', 'display: none;');
                    }
                } else {
                    $tpl->SetVariable('style', 'display: block;');
                }
                $tpl->SetVariable('name', $cat['category']);
                $tpl->SetVariable('description', $this->gadget->ParseText($cat['description']));
                if ($this->gadget->GetPermission('AddQuestion')) {
                    $add_url = BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion&amp;category='.$cat['id'];
                    $tpl->SetVariable('add_question', "<a href=\"{$add_url}\">"._t('FAQ_ADD_QUESTION').'</a>');
                }

                if ($this->gadget->GetPermission('ManageCategories')) {
                    $edit_url = BASE_SCRIPT . '?gadget=Faq&amp;action=EditCategory&amp;category='.$cat['id'];
                    $delete_url = "javascript:if (confirm('"._t('FAQ_CONFIRM_DELETE_CATEGORY').
                        "')) deleteCategory('".$cat['id']."'); return false;";
                    $tpl->SetVariable('edit', "<a href=\"{$edit_url}\">"._t('FAQ_EDIT_CATEGORY')."</a>");
                    $tpl->SetVariable('delete', "<a href=\"javascript:void(0);\" onclick=\"{$delete_url}\">"._t('FAQ_DELETE_CATEGORY')."</a>");
                }

                if (isset($cat['questions'])) {
                    $tpl->SetVariable('grid', $this->DataGrid($cat['id'], $cat['questions']));
                } else {
                    $tpl->SetVariable('grid', '');
                    $tpl->SetBlock('ManageQuestions/category/noquestions');
                    if ($this->gadget->GetPermission('AddQuestion')) {
                        $tpl->SetVariable('message', "<a href=\"{$add_url}\">"._t('FAQ_START_ADD')."</a>");
                    }
                    $tpl->ParseBlock('ManageQuestions/category/noquestions');
                }
                $tpl->ParseBlock('ManageQuestions/category');
                $i++;
            }
        }

        $tpl->ParseBlock('ManageQuestions');
        $manageTpl->SetVariable('ManageQuestions', $tpl->Get());
        $manageTpl->ParseBlock('Faq');

        return $manageTpl->Get();
    }

    /**
     * Edit a Question
     *
     * @access  public
     * @return  string  XHTML New question form content
     */
    function EditQuestion()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Faq.html');
        $tpl->SetBlock('edit_question');
        $tpl->SetVariable('menubar', $this->MenuBar('AddNewQuestion'));

        $qModel = $this->gadget->model->load('Question');
        $cModel = $this->gadget->model->load('Category');

        $get = jaws()->request->fetch(array('id', 'category'), 'get');

        //Add Faq Form
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->SetStyle('width: 100%;');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Faq'));
        if (!is_null($get['id'])) {
            $q = $qModel->GetQuestion($get['id']);
            if (Jaws_Error::IsError($q) || empty($q)) {
                Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq');
            }

            $form->Add(Piwi::CreateWidget('HiddenEntry', 'id', $get['id']));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateQuestion'));
        } else {
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'NewQuestion'));
        }

        if (!is_null($get['category'])) {
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'fromcategory', $get['category']));
        }
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'doPreview'));

        $qbox =& Piwi::CreateWidget('VBox');

        $catCombo =& Piwi::CreateWidget('Combo', 'category');
        $catCombo->SetTitle(_t('FAQ_CATEGORY'));
        $cats = $cModel->GetCategories();
        if (Jaws_Error::IsError($cats)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq');
        }

        $firstCategory = null;
        foreach ($cats as $c) {
            if (is_null($firstCategory)) {
                $firstCategory = $c['id'];
            }
            $catCombo->AddOption($c['category_position'].'. '.$c['category'], $c['id']);
        }

        if (!is_null($get['category'])) {
            $default = $get['category'];
        } else {
            $default = isset($q) ? $q['category_id'] : $firstCategory;
        }
        $catCombo->SetDefault($default);
        $qbox->PackStart($catCombo);

        $question = isset($q) ? $q['question'] : '';
        $qentry =& Piwi::CreateWidget('Entry', 'question', $question);
        $qentry->SetTitle(_t('FAQ_QUESTION'));
        $qentry->SetStyle('width: 500px;');
        $qbox->PackStart($qentry);

        $fasturl = isset($q) ? $q['fast_url'] : '';
        $qfasturl =& Piwi::CreateWidget('Entry', 'fast_url', $fasturl);
        $qfasturl->SetTitle(_t('FAQ_FASTURL'));
        $qfasturl->SetStyle('direction: ltr;');
        $qbox->PackStart($qfasturl);

        $answer = isset($q) ? $q['answer'] : '';
        $editor =& $GLOBALS['app']->LoadEditor('Faq', 'answer', $answer, false, _t('FAQ_ANSWER'));
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->TextArea->SetRows(8);
        $editor->setID('answer');

        $statCombo =& Piwi::CreateWidget('Combo', 'status');
        $statCombo->SetTitle(_t('FAQ_STATUS'));
        $statCombo->AddOption(_t('FAQ_ACTIVE'), 'yes');
        $statCombo->AddOption(_t('FAQ_INACTIVE'), 'no');
        $statCombo->SetDefault(isset($q['published']) && $q['published'] === false ? 'no' : 'yes');
        $sbox =& Piwi::CreateWidget('HBox');
        $sbox->PackStart($statCombo);

        $form->Add($qbox);
        $form->Add($editor);
        $form->Add($sbox);

        $qtext = isset($q) ? _t('FAQ_UPDATE_QUESTION') : _t('FAQ_ADD_QUESTION');
        $submit =& Piwi::CreateWidget('Button', 'updatequestion', $qtext, STOCK_SAVE);

        $submit->SetSubmit();
        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "javascript:window.location = '".BASE_SCRIPT . '?gadget=Faq'."';");
        $preview =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $preview->AddEvent(ON_CLICK, 'javascript:parseQuestionText(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($preview);
        $buttonbox->PackStart($cancel);
        $buttonbox->PackStart($submit);
        $form->Add($buttonbox);
        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('edit_question');

        return $tpl->Get();
    }

    /**
     * New question
     *
     * @access  public
     */
    function NewQuestion()
    {
        $this->gadget->CheckPermission('AddQuestion');
        $model = $this->gadget->model->loadAdmin('Question');

        $post = jaws()->request->fetch(array('question', 'fast_url', 'category', 'status'), 'post');
        $post['answer'] = jaws()->request->fetch('answer', 'post', 'strip_crlf');

        $model->AddQuestion($post['question'], $post['fast_url'], $post['answer'],
            $post['category'], ($post['status'] == 'yes'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq&category=' . $post['category']);
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

        $id = (int)jaws()->request->fetch('id', 'get');
        $model->DeleteQuestion($id);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq');
    }
}