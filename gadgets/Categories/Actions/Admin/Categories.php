<?php
/**
 * Categories Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Categories
 */
class Categories_Actions_Admin_Categories extends Categories_Actions_Admin_Default
{
    /**
     * Builds Categories UI
     *
     * @access  public
     * @param   string $req_gadget  Gadget name
     * @param   string $req_action  Action name
     * @param   string $menubar     Menubar
     * @return  string  XHTML UI
     */
    function Categories($req_gadget = '', $req_action = '', $menubar = '')
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', _t('GLOBAL_CONFIRM_DELETE'));
        $this->gadget->define('lbl_gadget', _t('CATEGORIES_GADGET'));
        $this->gadget->define('lbl_action', _t('CATEGORIES_ACTION'));
        $this->gadget->define('lbl_title', _t('GLOBAL_TITLE'));
        $this->gadget->define('lbl_edit', _t('GLOBAL_EDIT'));
        $this->gadget->define('lbl_delete', _t('GLOBAL_DELETE'));
        $this->gadget->define('req_gadget', $req_gadget);
        $this->gadget->define('req_action', $req_action);

        $tpl = $this->gadget->template->loadAdmin('Categories.html');
        $tpl->SetBlock('Categories');

        //Menu bar
        $tpl->SetVariable('menubar', empty($menubar)? $this->MenuBar('Categories') : $menubar);

        $tpl->SetVariable('lbl_of', _t('GLOBAL_OF'));
        $tpl->SetVariable('lbl_to', _t('GLOBAL_TO'));
        $tpl->SetVariable('lbl_items', _t('GLOBAL_ITEMS'));
        $tpl->SetVariable('lbl_per_page', _t('GLOBAL_PERPAGE'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_save', _t('GLOBAL_SAVE'));
        $tpl->SetVariable('lbl_add', _t('GLOBAL_ADD'));

        $tpl->SetVariable('lbl_term',   _t('GLOBAL_TERM'));
        $tpl->SetVariable('lbl_gadget', _t('CATEGORIES_GADGET'));
        $tpl->SetVariable('lbl_action', _t('CATEGORIES_ACTION'));
        $tpl->SetVariable('lbl_title',  _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_no',  _t('GLOBAL_NO'));
        $tpl->SetVariable('lbl_yes', _t('GLOBAL_YES'));
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_meta_title',  _t('GLOBAL_META_TITLE'));
        $tpl->SetVariable('lbl_meta_keywords',    _t('GLOBAL_META_KEYWORDS'));
        $tpl->SetVariable('lbl_meta_description', _t('GLOBAL_META_DESCRIPTION'));
        $tpl->SetVariable('lbl_meta_info', _t('GLOBAL_META_INFO'));

        $tpl->SetVariable('lbl_insert_time', _t('CATEGORIES_INSERT_TIME'));

        // gadgets filter
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList();

        // filters
        if (empty($req_gadget)) {
            $tpl->SetBlock('Categories/visible_inputs');
            $tpl->SetVariable('lbl_gadget', _t('CATEGORIES_GADGET'));
            $tpl->SetVariable('lbl_action', _t('CATEGORIES_ACTION'));
            if (!Jaws_Error::IsError($gadgetList) && count($gadgetList) > 0) {
                foreach ($gadgetList as $gadget) {
                    $tpl->SetBlock('Categories/visible_inputs/gadget');
                    $tpl->SetVariable('value', $gadget['name']);
                    $tpl->SetVariable('title', $gadget['title']);
                    $tpl->ParseBlock('Categories/visible_inputs/gadget');
                }
            }
            $tpl->ParseBlock('Categories/visible_inputs');

            $tpl->SetBlock('Categories/visible_filters');
            $tpl->SetVariable('lbl_gadget', _t('CATEGORIES_GADGET'));
            $tpl->SetVariable('lbl_action', _t('CATEGORIES_ACTION'));
            if (!Jaws_Error::IsError($gadgetList) && count($gadgetList) > 0) {
                array_unshift($gadgetList, array('name' => 0, 'title' => _t('GLOBAL_ALL')));
                foreach ($gadgetList as $gadget) {
                    $tpl->SetBlock('Categories/visible_filters/filter_gadget');
                    $tpl->SetVariable('value', $gadget['name']);
                    $tpl->SetVariable('title', $gadget['title']);
                    $tpl->ParseBlock('Categories/visible_filters/filter_gadget');
                }
            }
            $tpl->ParseBlock('Categories/visible_filters');

        } else {
            $tpl->SetBlock('Categories/hidden_filters');
            $tpl->SetVariable('gadget', $req_gadget);
            $tpl->SetVariable('action', $req_action);
            $tpl->ParseBlock('Categories/hidden_filters');

            $tpl->SetBlock('Categories/hidden_inputs');
            $tpl->SetVariable('gadget', $req_gadget);
            $tpl->SetVariable('action', $req_action);
            $tpl->ParseBlock('Categories/hidden_inputs');
        }

        $tpl->ParseBlock('Categories');
        return $tpl->Get();
    }

    /**
     * Get categories list
     *
     * @access  public
     * @return  JSON
     */
    function GetCategories()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $orderBy = 'nickname';
        if (isset($post['sort'])) {
            $orderBy = trim($post['sort'][0]['field'] . ' ' . $post['sort'][0]['direction']);
        }

        $model = $this->gadget->model->loadAdmin('Categories');
        $categories = $model->GetCategories($post['filters'], $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($categories)) {
            return $GLOBALS['app']->Session->GetResponse(
                $categories->getMessage(),
                RESPONSE_ERROR
            );
        }
        foreach ($categories as $key => $category) {
            $category['recid'] = $category['id'];
            $categories[$key] = $category;
        }
        $total = $model->GetCategoriesCount($post['filters']);
        if (Jaws_Error::IsError($total)) {
            return $GLOBALS['app']->Session->GetResponse(
                $total->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $categories
            )
        );
    }

    /**
     * Check a category exist
     *
     * @access  public
     * @return  Boolean
     */
    function CheckCategoryExist()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $filters = $this->gadget->request->fetch('filters:array', 'post');
        $exist = $this->gadget->model->loadAdmin('Categories')
            ->CheckCategoryExist($filters['gadget'], $filters['action'], $filters['title'] );
        if (Jaws_Error::IsError($exist)) {
            return false;
        }
        return $exist;
    }

    /**
     * Get a category info
     *
     * @access  public
     * @return  JSON
     */
    function GetCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $category = $this->gadget->model->loadAdmin('Categories')->GetCategory($id);
        if (Jaws_Error::IsError($category) || empty($category)) {
            return $GLOBALS['app']->Session->GetResponse(
                 empty($category)? _t('CATEGORIES_CATEGORY_NOTFOUND') : $category->getMessage(),
                RESPONSE_ERROR
            );
        }

        $category['insert_time'] = Jaws_Date::getInstance()->Format($category['insert_time']);
        return $GLOBALS['app']->Session->GetResponse(
            '',
            RESPONSE_NOTICE,
            $category
        );
    }

    /**
     * Insert a category
     *
     * @access  public
     * @return  void
     */
    function InsertCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');

        $data = $this->gadget->request->fetch('data:array', 'post');
        $result = $this->gadget->model->loadAdmin('Categories')->InsertCategory($data);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->GetMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                _t('CATEGORIES_CATEGORY_INSERTED'),
                RESPONSE_NOTICE,
                $result
            );
        }
    }

    /**
     * Update a category
     *
     * @access  public
     * @return  void
     */
    function UpdateCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');

        $post = $this->gadget->request->fetch(array('id', 'data:array'), 'post');
        $result = $this->gadget->model->loadAdmin('Categories')->UpdateCategory($post['id'], $post['data']);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('CATEGORIES_CATEGORY_UPDATED'), RESPONSE_NOTICE);
        }
    }

    /**
     * Delete a category
     *
     * @access  public
     * @return  void
     */
    function DeleteCategory()
    {
        $this->gadget->CheckPermission('ManageCategories');

        $id = (int)$this->gadget->request->fetch('id', 'post');
        $result =  $this->gadget->model->loadAdmin('Categories')->DeleteCategory($id);
        if (Jaws_Error::isError($result)) {
            return $GLOBALS['app']->Session->GetResponse($result->GetMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('CATEGORIES_CATEGORY_DELETED'), RESPONSE_NOTICE);
        }
    }

}