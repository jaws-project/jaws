<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Categories extends Blog_Actions_Admin_Default
{
    /**
     * Displays blog categories manager
     *
     * @access  public
     * @param   string  $second_action      
     * @return  string  XHTML template content
     */
    function ManageCategories($second_action = '')
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');
        $this->gadget->define('noImageURL', $GLOBALS['app']->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif'));

        $tpl = $this->gadget->template->loadAdmin('Categories.html');
        $tpl->SetBlock('categories');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('ManageCategories'));
        $tpl->SetVariable('categories', _t('BLOG_CATEGORIES'));

        $model = $this->gadget->model->load('Categories');
        $categories = $model->GetCategories();
        $combo =& Piwi::CreateWidget('Combo', 'category_id');
        $combo->SetID('category_id');
        $combo->SetStyle('width: 100%; margin-bottom: 10px;');
        $combo->SetSize(18);
        $combo->AddEvent(ON_CHANGE, 'editCategory(this.value)');
        foreach($categories as $cat) {
            $combo->AddOption($cat['name'], $cat['id']);
        }
        $tpl->SetVariable('combo', $combo->Get());

        // Logo
        $imageUrl = $GLOBALS['app']->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
        $logo =& Piwi::CreateWidget('Image', $imageUrl);
        $logo->SetID('image_preview');
        $tpl->SetVariable('image_preview', $logo->Get());

        $imageFile =& Piwi::CreateWidget('FileEntry', 'image_file', '');
        $imageFile->SetID('image_file');
        $imageFile->SetSize(1);
        $imageFile->SetStyle('width:110px; padding:0;');
        $imageFile->AddEvent(ON_CHANGE, 'uploadCategoryImage(this);');
        $tpl->SetVariable('image_file', $imageFile->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_upload', '', STOCK_ADD);
        $tpl->SetVariable('btn_upload', $button->Get());

        $button =& Piwi::CreateWidget('Button', 'btn_remove', '', STOCK_DELETE);
        $button->AddEvent(ON_CLICK, 'removeCategoryImage()');
        $tpl->SetVariable('btn_remove', $button->Get());


        // Category form
        $catName =& Piwi::CreateWidget('Entry', 'name', '');
        $catName->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_name', _t('BLOG_CATEGORY'));
        $tpl->SetVariable('name', $catName->Get());

        $catFastURL =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $catFastURL->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_fast_url', _t('BLOG_FASTURL'));
        $tpl->SetVariable('fast_url', $catFastURL->Get());

        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $metaKeywords->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_meta_keywords', _t('GLOBAL_META_KEYWORDS'));
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $metaDesc->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_meta_desc', _t('GLOBAL_META_DESCRIPTION'));
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        $catDescription =& Piwi::CreateWidget('TextArea', 'description', '');
        $catDescription->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $catDescription->Get());

        $btnDelete =& Piwi::CreateWidget('Button', 'btn_delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $btnDelete->AddEvent(ON_CLICK, 'javascript:deleteCategory();');
        $btnDelete->SetStyle('display: none;');
        $tpl->SetVariable('btn_delete', $btnDelete->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveCategory(this.form);');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('addCategory_title',_t('BLOG_NEW_CATEGORY'));
        $this->gadget->define('addCategory_title',_t('BLOG_NEW_CATEGORY'));
        $this->gadget->define('deleteMessage',_t('BLOG_DELETE_CONFIRM_CATEGORY'));
        $this->gadget->define('incompleteCategoryFields',_t('BLOG_CATEGORY_INCOMPLETE_FIELDS'));
        $this->gadget->define('editCategory_title',_t('BLOG_EDIT_CATEGORY'));

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
        $this->gadget->CheckPermission('ManageCategories');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->NewCategory(jaws()->request->fetch('catname', 'post'));

        return Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

    /**
     * Updates a blog category name
     *
     * @access  public
     */
    function UpdateCategory()
    {
        $post = jaws()->request->fetch(array('catid', 'catname'), 'post');

        $this->gadget->CheckPermission('ManageCategories');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->UpdateCategory($post['catid'], $post['catname']);

        return Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=EditCategory&id=' . $post['catid']);
    }

    /**
     * Deletes the given blog category
     *
     * @access  public
     */
    function DeleteCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->DeleteCategory(jaws()->request->fetch('catid', 'post'));

        return Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=ManageCategories');
    }

    /**
     * Uploads the attachment file
     *
     * @access  public
     * @return  array   File info
     */
    function UploadImage()
    {
        if (!isset($_FILES['file'])) {
            return $GLOBALS['app']->Session->GetResponse(_t('GLOBAL_ERROR_UPLOAD'), RESPONSE_ERROR);
        }
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), '', null);
        if (Jaws_Error::IsError($res) || !isset($res['file'][0])) {
            return $GLOBALS['app']->Session->GetResponse(_t('GLOBAL_ERROR_UPLOAD'), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('BLOG_IMAGE_UPLOADED'),
            RESPONSE_NOTICE,
            $res['file'][0]
        );
    }

}