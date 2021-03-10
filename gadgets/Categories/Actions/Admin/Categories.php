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
    function Categories($req_gadget = null, $req_action = null, $menubar = '')
    {
        $this->gadget->CheckPermission('ManageCategories');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('lbl_gadget', _t('CATEGORIES_GADGET'));
        $this->gadget->define('lbl_action', _t('CATEGORIES_ACTION'));
        $this->gadget->define('lbl_title', Jaws::t('TITLE'));
        $this->gadget->define('lbl_edit', Jaws::t('EDIT'));
        $this->gadget->define('lbl_delete', Jaws::t('DELETE'));
        $this->gadget->define('lbl_all', Jaws::t('ALL'));
        $this->gadget->define('req_gadget', $req_gadget);
        $this->gadget->define('req_action', $req_action);

        $model = $this->gadget->model->load('Categories');
        $gadgets = $model->getHookedGadgets();
        $gadgetsActions = array();
        $actions = array();
        if (count($gadgets) > 0) {
            foreach ($gadgets as $gadget) {
                // load gadget
                $objGadget = Jaws_Gadget::getInstance($gadget['name']);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }
                // load hook & execute hook
                $gActions = $objGadget->hook->load('Categories')->Execute();
                if (Jaws_Error::IsError($gActions)) {
                    continue;
                }

                if (!empty($req_gadget)) {
                    $actions = $gActions;
                }

                $gadgetsActions[$gadget['name']] = $gActions;
            }
        }

        $this->gadget->define('gadgets', array_column($gadgets, 'title', 'name'));
        $this->gadget->define('gadgets_actions', $gadgetsActions);

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Categories') : $menubar;
        $assigns['req_gadget'] = $req_gadget;
        $assigns['req_action'] = $req_action;
        $assigns['gadgets'] = $gadgets;
        $assigns['actions'] = $actions;
        $assigns['gadgets_actions'] = $gadgetsActions;
        return $this->gadget->template->xLoadAdmin('Categories.html')->render($assigns);
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
            return $this->gadget->session->response(
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
            return $this->gadget->session->response(
                $total->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $categories
            )
        );
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
            return $this->gadget->session->response(
                 empty($category)? _t('CATEGORIES_CATEGORY_NOTFOUND') : $category->getMessage(),
                RESPONSE_ERROR
            );
        }

        $category['insert_time'] = Jaws_Date::getInstance()->Format($category['insert_time']);
        return $this->gadget->session->response(
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
            return $this->gadget->session->response(
                $result->GetMessage(),
                RESPONSE_ERROR
            );
        }
        return $this->gadget->session->response(
            _t('CATEGORIES_CATEGORY_INSERTED'),
            RESPONSE_NOTICE,
            $result
        );
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
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('CATEGORIES_CATEGORY_UPDATED'), RESPONSE_NOTICE);
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
            return $this->gadget->session->response($result->GetMessage(), RESPONSE_ERROR);
        }
        return $this->gadget->session->response(_t('CATEGORIES_CATEGORY_DELETED'), RESPONSE_NOTICE);
    }

}