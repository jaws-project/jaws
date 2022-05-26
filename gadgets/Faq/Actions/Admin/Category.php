<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Actions_Admin_Category extends Faq_Actions_Admin_Default
{
    /**
     * Builds the administration UI for categories
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Categories()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Categories.html');
        $tpl->SetBlock('Categories');

        // Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Categories'));

        // Grid
        $tpl->SetVariable('grid', $this->CategoriesDataGrid());

        $entry =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE').':');
        $tpl->SetVariable('title', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $entry->SetStyle('direction:ltr;');
        $tpl->SetVariable('lbl_fast_url', $this::t('FASTURL').':');
        $tpl->SetVariable('fast_url', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $tpl->SetVariable('lbl_meta_keys', Jaws::t('META_KEYWORDS').':');
        $tpl->SetVariable('meta_keys', $entry->Get());

        $entry =& Piwi::CreateWidget('Entry', 'meta_description', '');
        $tpl->SetVariable('lbl_meta_desc', Jaws::t('META_DESCRIPTION').':');
        $tpl->SetVariable('meta_desc', $entry->Get());

        $description =& Piwi::CreateWidget('TextArea', 'description', '');
        $description->SetID('description');
        $description->SetRows(6);
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('description', $description->Get());

        $btnSave =& Piwi::CreateWidget('Button','btn_save', Jaws::t('SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveCategory();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnCancel =& Piwi::CreateWidget('Button','btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->SetVariable('legend_title',          $this::t('ADD_CATEGORY'));

        $this->gadget->define('addCategory_title',     $this::t('ADD_CATEGORY'));
        $this->gadget->define('editCategory_title',    $this::t('EDIT_CATEGORY'));
        $this->gadget->define('confirmCategoryDelete', $this::t('CONFIRM_DELETE_CATEGORY'));
        $this->gadget->define('incomplete_fields',     Jaws::t('ERROR_INCOMPLETE_FIELDS'));

        $tpl->ParseBlock('Categories');
        return $tpl->Get();
    }

    /**
     * Builds the categories data grid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function CategoriesDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('categories_datagrid');
        //$grid->TotalRows(25);
        $grid->pageBy(10);
        $column1 = Piwi::CreateWidget('Column', Jaws::t('TITLE'), null, false);
        $column1->SetStyle('white-space:nowrap;');
        $grid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column2->SetStyle('width:80px;');
        $grid->AddColumn($column2);
        $grid->SetStyle('margin-top: 0px; width: 100%;');

        return $grid->Get();
    }

    /**
     * Prepares data for categories data grid
     *
     * @access  public
     * @return  array   Grid data
     */
    function GetCategories()
    {
        $model = $this->gadget->model->load('Category');

        $categories = $model->GetCategories();
        if (Jaws_Error::IsError($categories)) {
            return array();
        }
        $result = array();
        foreach ($categories as $category) {
            $categoryData = array();

            $categoryData['title']  = $category['category'];

            $actions = '';
            if ($this->gadget->GetPermission('ManageCategories')) {
                $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                    "javascript:editCategory(this, '".$category['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', $this::t('MOVEUP'),
                    "javascript:moveCategory(" . $category['id'] . "," . $category['category_position'] . ", -1);",
                    STOCK_UP);
                $actions .= $link->Get() . '&nbsp;';

                $link =& Piwi::CreateWidget('Link', $this::t('MOVEDOWN'),
                    "javascript:moveCategory(" . $category['id'] . "," . $category['category_position'] . ", 1);",
                    STOCK_DOWN);
                $actions .= $link->Get() . '&nbsp;';

                $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                    "javascript:deleteCategory(this, '".$category['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $categoryData['actions'] = $actions;
            $result[] = $categoryData;
        }

        return $result;
    }


    /**
     * Gets the category data
     *
     * @access  public
     * @return  array   Category information
     */
    function GetCategory()
    {
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->load('Category');
        $category = $model->GetCategory($id);
        if (Jaws_Error::IsError($category)) {
            return false;
        }

        return $category;
    }

    /**
     * Gets the category data for grid
     *
     * @access  public
     * @return  string  XHTML grid data
     */
    function GetCategoriesGrid()
    {
        $this->gadget->CheckPermission('ManageCategories');
        @list($offset) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Category');

        return $gadget->GetCategoriesGrid($offset);
    }

    /**
     * Adds a new category
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $data = $this->gadget->request->fetch('data:array', 'post');
        $model = $this->gadget->model->loadAdmin('Category');
        $res = $model->InsertCategory($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('CATEGORY_ADDED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Updates the category
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Category');
        $res = $model->UpdateCategory($post['id'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CATEGORY_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('CATEGORY_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Deletes the category
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $model = $this->gadget->model->loadAdmin('Category');
        $res = $model->DeleteCategory($id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('CATEGORY_DELETED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Move a category
     *
     * @access   public
     * @return   array  Response array (notice or error)
     */
    function MoveCategory()
    {
        $post = $this->gadget->request->fetch(array('id', 'old_pos', 'new_pos'), 'post');
        $model = $this->gadget->model->loadAdmin('Category');
        $result = $model->MoveCategory($post['id'], $post['old_pos'], $post['new_pos']);
        if (Jaws_Error::IsError($result)) {
            return $this->gadget->session->response($this::t('ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('CATEGORY_MOVED'), RESPONSE_NOTICE);
        }
    }
}