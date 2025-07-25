<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
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
        $this->gadget->export('noImageURL', $this->app->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif'));

        $tpl = $this->gadget->template->loadAdmin('Categories.html');
        $tpl->SetBlock('categories');

        // Menubar
        $tpl->SetVariable('menubar', $this->MenuBar('ManageCategories'));
        $tpl->SetVariable('categories', $this::t('CATEGORIES'));

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
        $imageUrl = $this->app->getSiteURL('/gadgets/Blog/Resources/images/no-image.gif');
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
        $tpl->SetVariable('lbl_name', $this::t('CATEGORY'));
        $tpl->SetVariable('name', $catName->Get());

        $catFastURL =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $catFastURL->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_fast_url', $this::t('FASTURL'));
        $tpl->SetVariable('fast_url', $catFastURL->Get());

        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $metaKeywords->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_meta_keywords', Jaws::t('META_KEYWORDS'));
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_desc', '');
        $metaDesc->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_meta_desc', Jaws::t('META_DESCRIPTION'));
        $tpl->SetVariable('meta_desc', $metaDesc->Get());

        $catDescription =& Piwi::CreateWidget('TextArea', 'description', '');
        $catDescription->setStyle('width: 300px;');
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('description', $catDescription->Get());

        $btnDelete =& Piwi::CreateWidget('Button', 'btn_delete', Jaws::t('DELETE'), STOCK_DELETE);
        $btnDelete->AddEvent(ON_CLICK, 'javascript:deleteCategory();');
        $btnDelete->SetStyle('display: none;');
        $tpl->SetVariable('btn_delete', $btnDelete->Get());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save',Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveCategory(this.form);');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->SetVariable('addCategory_title',$this::t('NEW_CATEGORY'));
        $this->gadget->export('addCategory_title',$this::t('NEW_CATEGORY'));
        $this->gadget->export('deleteMessage',$this::t('DELETE_CONFIRM_CATEGORY'));
        $this->gadget->export('incompleteCategoryFields',$this::t('CATEGORY_INCOMPLETE_FIELDS'));
        $this->gadget->export('editCategory_title',$this::t('EDIT_CATEGORY'));

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
        $model->NewCategory($this->gadget->request->fetch('catname', 'post'));

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=ManageCategories');
    }

    /**
     * Updates a blog category name
     *
     * @access  public
     */
    function UpdateCategory()
    {
        $post = $this->gadget->request->fetch(array('catid', 'catname'), 'post');

        $this->gadget->CheckPermission('ManageCategories');
        $model = $this->gadget->model->loadAdmin('Categories');
        $model->UpdateCategory($post['catid'], $post['catname']);

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=EditCategory&id=' . $post['catid']);
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
        $model->DeleteCategory($this->gadget->request->fetch('catid', 'post'));

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Blog&reqAction=ManageCategories');
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
            return $this->gadget->session->response(Jaws::t('ERROR_UPLOAD'), RESPONSE_ERROR);
        }
        $res = Jaws_FileManagement_File::uploadFiles($_FILES, '', '', null);
        if (Jaws_Error::IsError($res) || !isset($res['file'][0])) {
            return $this->gadget->session->response(Jaws::t('ERROR_UPLOAD'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('IMAGE_UPLOADED'),
            RESPONSE_NOTICE,
            $res['file'][0]
        );
    }

}