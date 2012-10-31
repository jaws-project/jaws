<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Categories extends BlogAdminHTML
{
    /**
     * Get a list of categories in a combo
     *
     * @access   public
     * @param    array   $categories    Array of categories (optional)
     * @return   string  XHTML of a Combo
     */
    function GetCategoriesAsCombo($categories = null)
    {
        if (!is_array($categories)) {
            $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
            $categories = $model->GetCategories();
        }

        $combo =& Piwi::CreateWidget('Combo', 'category_id');
        $combo->SetID('category_id');
        $combo->SetStyle('width: 100%; margin-bottom: 10px;');
        $combo->SetSize(18);
        $combo->AddEvent(ON_CHANGE, 'editCategory(this.value)');

        foreach($categories as $cat) {
            $combo->AddOption($cat['name'], $cat['id']);
        }
        return $combo->Get();
    }


    /**
     * Get the categories form
     *
     * @access  public
     * @param   string  $second_action  
     * @param   int     $id             Category id
     * @return  string  XHTML template content
     */
    function CategoryForm($second_action = 'new', $id = '')
    {
        //Category form:
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Blog'));

        $name          = '';
        $description   = '';
        $fast_url      = '';
        $meta_keywords = '';
        $meta_desc     = '';
        if ($second_action == 'editcategory') {
            $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
            $item = $model->GetCategory($id);
            $name          = (isset($item['name'])) ? $item['name'] : '';
            $description   = (isset($item['description'])) ? $item['description'] : '';
            $fast_url      = (isset($item['fast_url'])) ? $item['fast_url'] : '';
            $meta_keywords = (isset($item['meta_keywords'])) ? $item['meta_keywords'] : '';
            $meta_desc     = (isset($item['meta_description'])) ? $item['meta_description'] : '';
        }

        $action = $second_action == 'editcategory' ? 'UpdateCategory' : 'AddCategory';
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', $action));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'catid', $id));

        $text = $second_action == 'editcategory' ? _t('BLOG_EDIT_CATEGORY') : _t('BLOG_NEW_CATEGORY');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet($text);
        // $fieldset =& Piwi::CreateWidget('FieldSet', $text);
        $fieldset->SetDirection('vertical');

        $catName =& Piwi::CreateWidget('Entry', 'name', $name);
        $catName->SetTitle(_t('BLOG_CATEGORY'));
        $catName->setStyle('width: 250px;');

        $catFastURL =& Piwi::CreateWidget('Entry', 'fast_url', $fast_url);
        $catFastURL->SetTitle(_t('BLOG_FASTURL'));
        $catFastURL->setStyle('width: 250px;');

        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', $meta_keywords);
        $metaKeywords->SetTitle(_t('GLOBAL_META_KEYWORDS'));
        $metaKeywords->setStyle('width: 250px;');

        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', $meta_desc);
        $metaDesc->SetTitle(_t('GLOBAL_META_DESCRIPTION'));
        $metaDesc->setStyle('width: 250px;');

        $catDescription =& Piwi::CreateWidget('TextArea', 'description', $description);
        $catDescription->SetTitle(_t('GLOBAL_DESCRIPTION'));
        $catDescription->setStyle('width: 250px;');

        $fieldset->Add($catName);
        $fieldset->Add($catFastURL);
        $fieldset->Add($metaKeywords);
        $fieldset->Add($metaDesc);
        $fieldset->Add($catDescription);
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');

        if ($second_action == 'editcategory') {
            $deletemenu =& Piwi::CreateWidget('Button', 'deletecategory', _t('GLOBAL_DELETE'), STOCK_DELETE);
            $deletemenu->AddEvent(ON_CLICK, "javascript: if (confirm('"._t('BLOG_DELETE_CONFIRM_CATEGORY')."')) ".
                                  "deleteCategory(this.form);");
            $buttonbox->Add($deletemenu);
        }

        $cancelmenu =& Piwi::CreateWidget('Button', 'cancelcategory', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelmenu->AddEvent(ON_CLICK, 'javascript: resetCategoryForm();');
        $buttonbox->Add($cancelmenu);

        $save =& Piwi::CreateWidget('Button', 'save',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveCategory(this.form);');
        $buttonbox->PackStart($save);

        $form->Add($buttonbox);

        return $form->Get();
    }

    /**
     * Displays blog categories manager
     *
     * @access  public
     * @param   string  $second_action      
     * @return  string  XHTML template content
     */
    function ManageCategories($second_action = '')
    {
        $this->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ManageCategories.html');
        $tpl->SetBlock('categories');

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('ManageCategories'));

        $tpl->SetBlock('categories/manage');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('categories', _t('BLOG_CATEGORIES'));

        //Category form:
        $tpl->SetVariable('new_form', $this->CategoryForm('new'));
        $tpl->SetVariable('delete_message',_t('BLOG_DELETE_CONFIRM_CATEGORY'));
        $tpl->SetVariable('combo', $this->GetCategoriesAsCombo());

        $new =& Piwi::CreateWidget('Button', 'new',_t('BLOG_NEW_CATEGORY'), STOCK_NEW);
        $new->SetStyle('width: 100%;');
        $new->AddEvent(ON_CLICK, 'javascript: newCategory();');
        $tpl->SetVariable('new_button', $new->Get());

        $tpl->ParseBlock('categories/manage');
        $tpl->ParseBlock('categories');

        return $tpl->Get();
    }

    /**
     * Adds the given category to blog
     *
     * @access  public
     */
    function AddCategory()
    {
        $request =& Jaws_Request::getInstance();

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->NewCategory($request->get('catname', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

    /**
     * Wrapper functions for EditCategory action
     * 
     * @access  public
     * @return  string  XHTML template content
     */
    function EditCategory()
    {
        return $this->ManageCategories('editcategory');
    }

    /**
     * Updates a blog category name
     *
     * @access  public
     */
    function UpdateCategory()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('catid', 'catname'), 'post');

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->UpdateCategory($post['catid'], $post['catname']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=EditCategory&id=' . $post['catid']);
    }

    /**
     * Deletes the given blog category
     *
     * @access  public
     */
    function DeleteCategory()
    {
        $request =& Jaws_Request::getInstance();

        $this->CheckPermission('ManageCategories');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'AdminModel');
        $model->DeleteCategory($request->get('catid', 'post'));

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

}