<?php
/**
 * Menu AJAX API
 *
 * @category    Ajax
 * @package     Menu
 */
class Menu_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Get all menus and groups data
     *
     * @access  public
     * @return  mixed   Data array or False on error
     */
    function GetMenusTrees()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        $data = $gadget->GetMenusTrees();
        unset($gadget);
        if (Jaws_Error::IsError($data)) {
            return false;
        }
        return $data;
    }

    /**
     * Returns the group form
     *
     * @access  public
     * @return  string  XHTML template of groupForm
     */
    function GetGroupUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        return $gadget->GetGroupUI();
    }

    /**
     * Returns the menu form
     *
     * @access  public
     * @return  string  XHTML template of groupForm
     */
    function GetMenuUI()
    {
        $gadget = $this->gadget->action->loadAdmin('Menu');
        return $gadget->GetMenuUI();
    }

    /**
     * Get information of a group
     *
     * @access  public
     * @return  mixed   Group information array or False on error
     */
    function GetGroups()
    {
        @list($gid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Group');
        $groupInfo = $model->GetGroups($gid);
        if (Jaws_Error::IsError($groupInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $groupInfo;
    }

    /**
     * Get menu data
     *
     * @access  public
     * @return  mixed   Menu data array or False on error
     */
    function GetMenu()
    {
        @list($mid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Menu');
        $menu = $model->GetMenu($mid);
        if (Jaws_Error::IsError($menu)) {
            return false; //we need to handle errors on ajax
        }

        if (false === @unserialize($menu['url'])) {
            $menu['url'] = Jaws_XSS::defilterURL($menu['url']);
        }
        // menu options
        $menu['options'] = @unserialize($menu['options']);
        if (!empty($menu['options'])) {
            $menu['options'] = http_build_query($menu['options']);
        } else {
            $menu['options'] = '';
        }

        return $menu;
    }

    /**
     * Insert group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($title, $home, $title_view, $view_type, $published) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->InsertGroup($title, $home, $title_view, $view_type, (bool)$published);

        return $this->gadget->session->pop();
    }

    /**
     * Insert menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function InsertMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($pid, $gid, $gadget, $permission, $title, $url, $variables, $options, $symbol, $target,
            $order, $status, $image
        ) = $this->gadget->request->fetchAll('post');

        if (is_null($url)) {
            $url = serialize($this->gadget->request->fetch('5:array', 'post'));
        } else {
            // parse & encode given url
            $url = Jaws_XSS::filterURL($url);
        }

        if (is_null($permission)) {
            $permission = serialize($this->gadget->request->fetch('3:array', 'post'));
        }
        if (is_null($variables)) {
            $variables = serialize($this->gadget->request->fetch('6:array', 'post'));
        }
        // parse & serialize menu options
        parse_str(Jaws_XSS::filterURL($options), $options);
        $options = serialize($options);

        $mData = array(
            'pid'        => $pid,
            'gid'        => $gid,
            'gadget'     => $gadget,
            'permission' => $permission,
            'title'      => $title,
            'url'        => $url,
            'variables'  => $variables,
            'options'    => $options,
            'symbol'     => $symbol,
            'target'     => $target,
            'order'      => $order, 
            'status'     => (int)$status,
            'image'      => $image
        );
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->InsertMenu($mData);

        return $this->gadget->session->pop();
    }

    /**
     * Update group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list(
            $gid, $title, $home, $title_view, $view_type, $published
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->UpdateGroup($gid, $title, $home, $title_view, $view_type, (bool)$published);

        return $this->gadget->session->pop();
    }

    /**
     * Update menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid, $pid, $gid, $gadget, $permission, $title, $url, $variables, $options, $symbol, $target,
            $order, $status, $image
        ) = $this->gadget->request->fetchAll('post');

        if (is_null($url)) {
            $url = serialize($this->gadget->request->fetch('6:array', 'post'));
        } else {
            // parse & encode given url
            $url = Jaws_XSS::filterURL($url);
        }

        if (is_null($permission)) {
            $permission = serialize($this->gadget->request->fetch('4:array', 'post'));
        }
        if (is_null($variables)) {
            $variables  = serialize($this->gadget->request->fetch('7:array', 'post'));
        }
        // parse & serialize menu options
        parse_str(Jaws_XSS::filterURL($options), $options);
        $options = serialize($options);

        $mData = array(
            'pid'        => $pid,
            'gid'        => $gid,
            'gadget'     => $gadget,
            'permission' => $permission,
            'title'      => $title,
            'url'        => $url,
            'variables'  => $variables,
            'options'    => $options,
            'symbol'     => $symbol,
            'target'     => $target,
            'order'      => $order, 
            'status'     => (int)$status,
            'image'      => $image
        );
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->UpdateMenu($mid, $mData);

        return $this->gadget->session->pop();
    }

    /**
     * Delete an group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteGroup()
    {
        $this->gadget->CheckPermission('ManageGroups');
        @list($gid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Group');
        $model->DeleteGroup($gid);

        return $this->gadget->session->pop();
    }

    /**
     * Delete an menu
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $result = $model->DeleteMenu($mid);
        if ($result) {
            $this->gadget->session->push($this::t('NOTICE_MENU_DELETED'), RESPONSE_NOTICE);
        }

        return $this->gadget->session->pop();
    }

    /**
     * Get menu data
     *
     * @access  public
     * @return  array   Menu data array
     */
    function GetParentMenus()
    {
        @list($gid, $mid) = $this->gadget->request->fetchAll('post');
        $result[] = array('pid'=> 0,
                          'title'=>'\\');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->GetParentMenus(0, $gid, $mid, $result);

        return $result;
    }

    /**
     * function for change gid, pid and order of menus
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MoveMenu()
    {
        $this->gadget->CheckPermission('ManageMenus');
        @list($mid, $new_gid, $old_gid, $new_pid, $old_pid,
            $new_order, $old_order
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Menu');
        $model->MoveMenu($mid, $new_gid, $old_gid, $new_pid, $old_pid, $new_order, $old_order);

        return $this->gadget->session->pop();
    }

    /**
     * Get a list of URLs of a gadget
     *
     * @access  public
     * @return  array   URLs array on success or empty array on failure
     */
    function GetPublicURList()
    {
        @list($request) = $this->gadget->request->fetchAll('post');
        if ($request == 'url') {
            $urls[] = array('url'   => '/',
                            'title' => $this::t('REFERENCES_FREE_LINK'));
            $urls[] = array('url'   => '',
                            'title' => $this::t('REFERENCES_NO_LINK'));
            return $urls;
        } else {
            if (Jaws_Gadget::IsGadgetUpdated($request)) {
                $objGadget = Jaws_Gadget::getInstance($request);
                if (!Jaws_Error::IsError($objGadget)) {
                    $links = $objGadget->hook->load('Menu')->Execute();
                    if (!Jaws_Error::IsError($links)) {
                        array_unshift(
                            $links,
                            array(
                                'url'   => '/',
                                'title' => $this::t('REFERENCES_FREE_LINK')
                            ),
                            array(
                                'url'   => '',
                                'title' => $this::t('REFERENCES_NO_LINK')
                            )
                        );
                        foreach ($links as $key => $link) {
                            if (is_array($link['url'])) {
                                $links[$key]['url'] = serialize($link['url']);
                            } else {
                                $links[$key]['url'] = rawurldecode($link['url']);
                            }
                            // serialize variables
                            if (isset($link['variables'])) {
                                $links[$key]['variables'] = serialize($link['variables']);
                            }
                            // query options
                            if (isset($link['options'])) {
                                $links[$key]['options'] = http_build_query($link['options']);
                            }
                            // serialize permission
                            if (isset($link['permission'])) {
                                $links[$key]['permission'] = serialize($link['permission']);
                            }
                        }

                        return $links;
                    }
                }
            }
        }

        return array();
    }

}