<?php
/**
 * Menu Gadget
 *
 * @category    Gadget
 * @package     Menu
 */
class Menu_Actions_Menu extends Jaws_Gadget_Action
{
    /**
     * Request URL
     *
     * @var     string
     * @access  private
     */
    var $_ReqURL = '';

    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function MenuLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Group');
        $groups = $model->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => $this::t('ACTIONS_MENU'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Displays the menus with their items
     *
     * @access  public
     * @param   int     $gid    Menu group ID
     * @return  string  XHTML template content
     */
    function Menu($gid = 0)
    {
        $group = $this->gadget->model->load('Group')->GetGroups($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('title', $group['title']);
        $this->_ReqURL = Jaws_Utils::getRequestURL();
        $this->_ReqURL = str_replace(BASE_SCRIPT, '', $this->_ReqURL);

        $assigns = array();
        $assigns['group'] = $group;
        $assigns['home'] = array();

        // home/brand menu
        if ($group['title_view'] == 1) {
            if (!empty($group['home'])) {
                $homeMenu = $this->gadget->model->load('Menu')->GetMenu($group['home']);
                if (!$this->ParseMenu($homeMenu)) {
                    $homeMenu = array();
                }
            } else {
                $homeMenu = array();
                $homeMenu['symbol'] = 'glyphicon glyphicon-home';
                $homeMenu['url']    = '/';
                $homeMenu['title']  = $this::t('HOME');
            }

            $assigns['home'] = $homeMenu;
        }

        $assigns['levels'] = array();
        $this->GetNextLevel($assigns['levels'], $group['id'], 0);
        return $this->gadget->template->xLoad('Menu'. $group['view_type'].'.html')->render($assigns);
    }

    /**
     * Gets next level of parent menu
     *
     * @access  public
     * @param   array   $levels Menu levels
     * @param   int     $gid    Group ID
     * @param   int     $pid    Parent Menu
     * @return  void
     */
    private function GetNextLevel(&$levels, $gid, $pid)
    {
        $menus = $this->gadget->model->load('Menu')->GetLevelsMenus($pid, $gid, true);
        if (Jaws_Error::IsError($menus) || empty($menus)) {
            return false;
        }

        $levels = array();
        $logged = $this->app->session->user->logged;
        foreach ($menus as $i => $menu) {
            // parse menu
            if (!$this->ParseMenu($menu)) {
                continue;
            }
            $levels[$menu['id']] = $menu;
            //menu selected?
            $levels[$menu['id']]['selected'] = str_replace(BASE_SCRIPT, '', urldecode($menu['url'])) == $this->_ReqURL;

            $levels[$menu['id']]['items'] = false;
            //get sub level menus
            $this->GetNextLevel($levels[$menu['id']]['items'], $gid, $menu['id']);
        }
    }

    /**
     * Displays/Parse a menu
     *
     * @access  public
     * @param   array   $menu   Menu attributes
     * @return  bool    True if parsed otherwise False
     */
    private function ParseMenu(&$menu)
    {
        $logged = $this->app->session->user->logged;

        // is menu viewable?
        if ($menu['status'] == 0) {
            return false;
        }

        if ($menu['status'] != 1) {
            if ($logged xor $menu['status'] == Menu_Info::STATUS_LOGGED_IN) {
                return false;
            }
        }

        // check default ACL
        if ($menu['gadget'] != 'url') {
            if (!Jaws_Gadget::IsGadgetInstalled($menu['gadget'])) {
                return false;
            }

            if (!$this->app->session->getPermission($menu['gadget'], 'default')) {
                return false;
            }

            // check permission
            if (!empty($menu['permission'])) {
                $permission = unserialize($menu['permission']);
                if (!empty($permission)) {
                    if (isset($permission['gadget'])) {
                        if (!$this->app->session->getPermission($permission['gadget'], 'default')) {
                            return false;
                        }
                    } else {
                        $permission['gadget'] = $menu['gadget'];
                    }

                    if (!$this->app->session->getPermission(
                            $permission['gadget'],
                            $permission['key'],
                            isset($permission['subkey'])? $permission['subkey'] : '',
                            isset($permission['together'])? (bool)$permission['together'] : true
                        )
                    ) {
                        return false;
                    }
                }
            }
        }

        // replace menu variables
        if (!empty($menu['variables'])) {
            $objGadget = Jaws_Gadget::getInstance($menu['gadget']);
            if (Jaws_Error::IsError($objGadget)) {
                return false;
            }

            $params = array();
            $vars = unserialize($menu['variables']);
            $url  = unserialize($menu['url']);
            foreach ($vars as $var => $val) {
                switch (@$val['scope']) {
                    case SESSION_SCOPE_APP:
                        $val = $this->app->session->{$val['name']};
                        break;

                    case SESSION_SCOPE_USER:
                        $val = $this->app->session->user->{$val['name']};
                        break;

                    case SESSION_SCOPE_GADGET:
                        $val = $objGadget->session->{$val['name']};
                        break;

                    default:
                        $val = null;
                }

                // if variable is null or undefined ignore this menu
                if (is_null($val)) {
                    return false;
                }
                // if variable not in menu url parameters ignore this variable
                if (!array_key_exists($var, $url['params'])) {
                    continue;
                }
                // set url variable
                $params[$var] = Jaws_UTF8::str_replace('{' . $var . '}', $val, $url['params'][$var]);

                // set title variables
                $menu['title'] = Jaws_UTF8::str_replace('{' . $var . '}', $val, $menu['title']);
            }

            // menu options
            $menu['options'] = @unserialize($menu['options']);
            if (empty($menu['options'])) {
                $menu['options'] = array();
            }

            // generate url map
            $menu['url'] = $objGadget->urlMap(
                $url['action'],
                $params,
                $menu['options'],
                isset($url['gadget'])? $url['gadget'] : ''
            );
        }

        return true;
    }

    /**
     * Returns navigation of hierarchical structure 
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Breadcrumb()
    {
        $assigns = array();
        $assigns['items'] = array_reverse($this->app->breadcrumb);
        return $this->gadget->template->xLoad('Breadcrumb.html')->render($assigns);
    }

    /**
     * Returns menu image as stream data
     *
     * @access  public
     * @return  bool    True on successful, False otherwise
     */
    function LoadImage()
    {
        $id = (int)$this->gadget->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Menu');
        $image = $model->GetMenuImage($id);
        if (!Jaws_Error::IsError($image)) {
            $objImage = Jaws_Image::factory();
            if (!Jaws_Error::IsError($objImage)) {
                $objImage->setData($image, true);
                $res = $objImage->display('', null, 2592000);// cached for a month
                if (!Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        return false;
    }
}