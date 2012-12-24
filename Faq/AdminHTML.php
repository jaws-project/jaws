<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Builds the menubar
     *
     * @access  public
     * @param   string  $selected   Selected action
     * @return  string  XHTML menu template
     */
    function MenuBar($selected)
    {
        $actions = array('ManageQuestions', 'AddNewQuestion', 'AddNewCategory');

        if (!in_array($selected, $actions)) {
            $selected = 'ManageQuestions';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('ManageQuestions', _t('FAQ_LIST'),
                            BASE_SCRIPT . '?gadget=Faq&amp;action=ManageQuestions', STOCK_DOCUMENTS);

        if ($this->gadget->GetPermission('AddNewQuestion')) {
            $menubar->AddOption('AddNewQuestion', _t('FAQ_ADD_QUESTION'),
                                BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion', STOCK_NEW);
        }

        if ($this->gadget->GetPermission('ManageCategories')) {
            $menubar->AddOption('AddNewCategory', _t('FAQ_ADD_CATEGORY'),
                                BASE_SCRIPT . '?gadget=Faq&amp;action=EditCategory', STOCK_NEW);
        }

        $menubar->Activate($selected);

        return $menubar->Get();
    }

    /**
     * Displays faq admin section
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        return $this->ManageQuestions();
    }

    /**
     * Creates the datagrid of a category
     *
     * @access   public
     * @param    int     $cat           Category
     * @param    array   $questions     Array of questions so that we can skip fetching those here
     * @param    int     $maxCatPos     Max cat position
     * @return   string  XHTML template of datagrid
     */
    function DataGrid($cat, $questions = null, $maxCatPos = null)
    {
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');
        $foo = $questions;
        if ($questions === null || $maxCatPos === null) {
            $questions = $model->GetQuestions($cat);
            $maxCatPos = $model->GetMaxCategoryPosition();
        }

        if (is_array($questions) && count($questions) > 0) {
            // Checking if position 0 is set since getQuestions will never create position 0
            if (!isset($questions[0])) {
                // First return the first array back
                $questions = array_shift($questions);
                // Now assign the questions to $questions ;-)
                $questions = $questions['questions'];
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
            $grid->AddColumn(Piwi::CreateWidget('ActionColumn', _t('FAQ_MOVEUP'),
                                                "javascript: moveQuestion('{id}', '{category}', 'UP'); return false;",
                                                STOCK_UP));
            $grid->AddColumn(Piwi::CreateWidget('ActionColumn', _t('FAQ_MOVEDOWN'),
                                                "javascript: moveQuestion('{id}', '{category}', 'DOWN'); return false;",
                                                STOCK_DOWN));
            if ($this->gadget->GetPermission('DeleteQuestion')) {
                $grid->AddColumn(Piwi::CreateWidget('ActionColumn', _t('GLOBAL_DELETE'),
                                                    "javascript: if (confirm('"._t('FAQ_CONFIRM_DELETE_QUESTION').
                                                    "')) deleteQuestion('{id}', '{category}'); return false;",
                                                    STOCK_DELETE));
            }
            return $grid->Get();
        }

        return '';
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

        $request  =& Jaws_Request::getInstance();
        $category = $request->get('category', 'get');

        $manageTpl = new Jaws_Template('gadgets/Faq/templates/');
        $manageTpl->Load('AdminFaq.html');
        $manageTpl->SetBlock('Faq');
        $manageTpl->SetVariable('base_script', BASE_SCRIPT);
        $manageTpl->SetVariable('menubar', $this->MenuBar(''));

        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $catCombo =& Piwi::CreateWidget('Combo', 'category');
        $cats = $model->GetCategories();
        $catCombo->AddOption(_t('FAQ_ALL_CATEGORIES'), '*');
        foreach ($cats as $c) {
            $catCombo->AddOption($c['category_position'].'. '.$c['category'], $c['id']);
        }

        if (isset($category)) {
            $catCombo->SetDefault($category);
        } else {
            $catCombo->SetDefault('*');
        }

        $catCombo->AddEvent(ON_CHANGE, 'javascript: showCategory(this.form);');
        $manageTpl->SetVariable('category', _t('FAQ_CATEGORY'));
        $manageTpl->SetVariable('category_combo', $catCombo->Get());

        ///OK.. build the complete work area..
        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('AdminFaq.html');
        $tpl->SetBlock('ManageQuestions');

        $questions = $model->GetQuestions();
        $maxCatPos = $model->GetMaxCategoryPosition();
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
                $tpl->SetVariable('description', $this->gadget->ParseText($cat['description'], 'Faq'));
                if ($this->gadget->GetPermission('AddQuestion')) {
                    $add_url = BASE_SCRIPT . '?gadget=Faq&amp;action=EditQuestion&amp;category='.$cat['id'];
                    $tpl->SetVariable('add_question', "<a href=\"{$add_url}\">"._t('FAQ_ADD_QUESTION').'</a>');
                }

                if ($this->gadget->GetPermission('ManageCategories')) {
                    $edit_url = BASE_SCRIPT . '?gadget=Faq&amp;action=EditCategory&amp;category='.$cat['id'];
                    $delete_url = "javascript: if (confirm('"._t('FAQ_CONFIRM_DELETE_CATEGORY').
                        "')) deleteCategory('".$cat['id']."'); return false;";
                    $tpl->SetVariable('edit', "<a href=\"{$edit_url}\">"._t('FAQ_EDIT_CATEGORY')."</a>");
                    $tpl->SetVariable('delete', "<a href=\"javascript:void(0);\" onclick=\"{$delete_url}\">"._t('FAQ_DELETE_CATEGORY')."</a>");
                }

                if (isset($cat['questions'])) {
                    $tpl->SetVariable('grid', $this->DataGrid($cat['id'], $cat['questions'], $maxCatPos));
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

        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('AdminFaq.html');
        $tpl->SetBlock('edit_question');
        $tpl->SetVariable('menubar', $this->MenuBar('AddNewQuestion'));

        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('id', 'category'), 'get');

        //Add Faq Form
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->SetStyle('width: 100%;');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Faq'));
        if (!is_null($get['id'])) {
            $q = $model->GetQuestion($get['id']);
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
        $cats = $model->GetCategories();
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
        $editor =& $GLOBALS['app']->LoadEditor('Faq', 'answer', $answer, false);
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('750px');
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
        $cancel->AddEvent(ON_CLICK, "javascript: window.location = '".BASE_SCRIPT . '?gadget=Faq&amp;action=Admin'."';");
        $preview =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $preview->AddEvent(ON_CLICK, 'javascript: parseQuestionText(this.form);');

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
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('question', 'fast_url', 'category', 'status'), 'post');
        $post['answer'] = $request->get('answer', 'post', false);

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
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('id', 'question', 'fast_url', 'category', 'status'), 'post');
        $post['answer'] = $request->get('answer', 'post', false);

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
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $id      = (int)$request->get('id', 'get');

        $model->DeleteQuestion($id);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq');
    }

    /**
     * Edit a category
     *
     * @access  public
     * @return  string  XHTML Category form content
     */
    function EditCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');

        $request  =& Jaws_Request::getInstance();
        $category = $request->get('category', 'get');

        if (!is_null($category)) {
            $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');
            $cat = $model->GetCategory($category);
        }
        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('AdminFaq.html');
        $tpl->SetBlock('edit_category');
        $tpl->SetVariable('menubar', $this->MenuBar('AddNewCategory'));

        //Add Faq Form
        $faqform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $faqform->SetStyle('width: 100%;');
        $faqform->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Faq'));
        if (isset($cat)) {
            $faqform->Add(Piwi::CreateWidget('HiddenEntry', 'id', $category));
            $faqform->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateCategory'));
        } else {
            $faqform->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'NewCategory'));
        }

        $catbox =& Piwi::CreateWidget('VBox');
        $catTitle = isset($cat) ? $cat['category'] : '';
        $catentry =& Piwi::CreateWidget('Entry', 'category', $catTitle);
        $catentry->SetTitle(_t('FAQ_CATEGORY'));
        $catentry->SetStyle('width: 500px;');
        $catbox->PackStart($catentry);

        $fasturl = isset($cat) ? $cat['fast_url'] : '';
        $cfasturl =& Piwi::CreateWidget('Entry', 'fast_url', $fasturl);
        $cfasturl->SetTitle(_t('FAQ_FASTURL'));
        $cfasturl->SetStyle('direction: ltr; width: 500px;');
        $catbox->PackStart($cfasturl);

        $desc = isset($cat) ? $cat['description'] : '';
        $editor =& $GLOBALS['app']->LoadEditor('Faq', 'description', $desc, false);
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->SetWidth('750px');

        $faqform->Add($catbox);
        $faqform->Add($editor);
        if (isset($cat)) {
            $submit =& Piwi::CreateWidget('Button', 'updcat', _t('FAQ_UPDATE_CATEGORY'), STOCK_SAVE);
        } else {
            $submit =& Piwi::CreateWidget('Button', 'newcat', _t('FAQ_ADD_CATEGORY'), STOCK_SAVE);
        }
        $submit->SetSubmit();
        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "javascript: window.location = '".BASE_SCRIPT.'?gadget=Faq&amp;action=Admin'."';");
        $preview =& Piwi::CreateWidget('Button', 'previewButton', _t('GLOBAL_PREVIEW'), STOCK_PRINT_PREVIEW);
        $preview->AddEvent(ON_CLICK, 'javascript: parseCategoryText(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($preview);
        $buttonbox->PackStart($cancel);
        $buttonbox->PackStart($submit);
        $faqform->Add($buttonbox);
        $tpl->SetVariable('form', $faqform->Get());

        $tpl->ParseBlock('edit_category');
        return $tpl->Get();
    }

    /**
     * New category
     * 
     * @access  public
     */
    function NewCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('category', 'fast_url'), 'post');
        $post['description'] = $request->get('description', 'post', false);

        $id = $model->AddCategory($post['category'], $post['fast_url'], $post['description']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq&category=' . $id);
    }

    /**
     * Update category
     * 
     * @access  public
     */
    function UpdateCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Faq', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('id', 'category', 'fast_url'), 'post');
        $post['description'] = $request->get('description', 'post', false);

        $model->UpdateCategory($post['id'], $post['category'], $post['fast_url'], $post['description']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq&category='. $post['id']);
    }

}