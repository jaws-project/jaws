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
     * Creates the datagrid of a category
     *
     * @access   public
     * @param    int     $cat           Category
     * @param    array   $questions     Array of questions so that we can skip fetching those here
     * @return   string  XHTML template of datagrid
     */
    function DataGrid($cat, $questions = null)
    {
        $model = $this->gadget->model->load('Question');
        if ($questions === null) {
            $questions = $model->GetQuestions($cat);
            $questions = array_shift($questions);
            if (isset($questions['questions'])) {
                $questions = $questions['questions'];
            } else {
                $questions = array();
            }
        }

        if (Jaws_Error::IsError($questions) || empty($questions)) {
            return '';
        }

        $grid =& Piwi::CreateWidget('DataGrid', $questions, null);
        $grid->SetStyle('width: 100%;');
        $colPos =& Piwi::CreateWidget('Column', '#', 'position', false);
        $colPos->SetStyle('text-align: center;');
        $grid->AddColumn($colPos);

        $grid->AddColumn(Piwi::CreateWidget('Column', _t('FAQ_QUESTION'), 'question', false, 'String', true,
            BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion&amp;id={id}'));
        if ($this->gadget->GetPermission('EditQuestion')) {
            $grid->AddColumn(Piwi::CreateWidget('ActionColumn', _t('GLOBAL_EDIT'),
                BASE_SCRIPT . "?gadget=Faq&amp;action=EditQuestion&amp;".
                "id={id}&amp;category={$cat}", STOCK_EDIT));
        }
        $grid->AddColumn(Piwi::CreateWidget(
                'ActionColumn',
                _t('FAQ_MOVEUP'),
                "javascript:moveQuestion({category}, {id}, {position}, -1); return false;",
                STOCK_UP)
        );
        $grid->AddColumn(Piwi::CreateWidget(
                'ActionColumn',
                _t('FAQ_MOVEDOWN'),
                "javascript:moveQuestion({category}, {id}, {position}, 1); return false;",
                STOCK_DOWN)
        );
        if ($this->gadget->GetPermission('DeleteQuestion')) {
            $grid->AddColumn(Piwi::CreateWidget('ActionColumn', _t('GLOBAL_DELETE'),
                "javascript:if (confirm('"._t('FAQ_CONFIRM_DELETE_QUESTION').
                "')) deleteQuestion('{id}', '{category}'); return false;",
                STOCK_DELETE));
        }

        return $grid->Get();
    }

    /**
     * Displays faq list of questions(admin mode)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageQuestions()
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
        $qfasturl->SetStyle('direction: ltr; width: 500px;');
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
     * New question
     *
     * @access  public
     */
    function UpdateQuestion()
    {
        $this->gadget->CheckPermission('EditQuestion');
        $model = $this->gadget->model->loadAdmin('Question');

        $post    = jaws()->request->fetch(array('id', 'question', 'fast_url', 'category', 'status'), 'post');
        $post['answer'] = jaws()->request->fetch('answer', 'post', 'strip_crlf');

        $model->UpdateQuestion($post['id'], $post['question'], $post['fast_url'],
            $post['answer'], $post['category'], ($post['status'] == 'yes'));

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