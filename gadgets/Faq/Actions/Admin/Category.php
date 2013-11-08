<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Actions_Admin_Category extends Faq_Actions_Admin_Default
{
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

        $category = jaws()->request->fetch('category', 'get');
        if (!is_null($category)) {
            $model = $this->gadget->model->loadAdmin('Category');
            $cat = $model->GetCategory($category);
        }

        $tpl = $this->gadget->template->loadAdmin('Faq.html');
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
        $editor =& $GLOBALS['app']->LoadEditor('Faq', 'description', $desc, false, _t('GLOBAL_DESCRIPTION'));
        $editor->TextArea->SetStyle('width: 100%;');
        $editor->TextArea->SetRows(8);

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
        $model = $this->gadget->model->loadAdmin('Category');

        $post    = jaws()->request->fetch(array('category', 'fast_url'), 'post');
        $post['description'] = jaws()->request->fetch('description', 'post', false);

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
        $model = $this->gadget->model->loadAdmin('Category');

        $post    = jaws()->request->fetch(array('id', 'category', 'fast_url'), 'post');
        $post['description'] = jaws()->request->fetch('description', 'post', false);

        $model->UpdateCategory($post['id'], $post['category'], $post['fast_url'], $post['description']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Faq&category='. $post['id']);
    }

}