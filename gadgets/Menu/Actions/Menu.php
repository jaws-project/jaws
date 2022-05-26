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
        $gModel = $this->gadget->model->load('Group');
        $group = $gModel->GetGroups($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('title', $group['title']);
        $this->_ReqURL = Jaws_Utils::getRequestURL();
        $this->_ReqURL = str_replace(BASE_SCRIPT, '', $this->_ReqURL);

        $tpl = $this->gadget->template->load(
            $group['view_type'] == 2? 'Menu2.html' : 'Menu.html',
            array('rawStore' => true)
        );
        $tpl_str = $tpl->GetContent();
        $tpl->SetBlock('menu');
        $tpl->SetVariable('gid', $group['id']);

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

            if (!empty($homeMenu)) {
                $tpl->SetBlock('menu/home');
                $tpl->SetVariable('symbol', $homeMenu['symbol']);
                $tpl->SetVariable('url',    $homeMenu['url']);
                $tpl->SetVariable('title',  $homeMenu['title']);
                $tpl->ParseBlock('menu/home');
            }
        }

        $tpl->SetVariable('menus_tree', $this->GetNextLevel($tpl_str, $group['id'], 0, -1));
        $tpl->ParseBlock('menu');
        return $tpl->Get();
    }

    /**
     * Displays the next level of parent menu
     *
     * @access  public
     * @param   string  $tpl_str    XHTML template content passed by reference
     * @param   int     $gid        Group ID
     * @param   int     $pid        Parent Menu
     * @param   int     $level      Menu level
     * @return  string  XHTML template content with sub menu items
     */
    function GetNextLevel(&$tpl_str, $gid, $pid, $level)
    {
        $level++;
        $menus = $this->gadget->model->load('Menu')->GetLevelsMenus($pid, $gid, true);
        if (Jaws_Error::IsError($menus) || empty($menus)) {
            return '';
        }

        $tpl = new Jaws_Template();
        $tpl->LoadFromString($tpl_str);
        $block = empty($pid)? 'mainmenu' : 'submenu';
        $tpl->SetBlock("$block");

        $len = count($menus);
        $logged = $this->app->session->user->logged;
        foreach ($menus as $i => $menu) {
            // parse menu
            if (!$this->ParseMenu($menu)) {
                continue;
            }

            //get sub level menus
            $submenu = $this->GetNextLevel($tpl_str, $gid, $menu['id'], $level);
            $innerBlock = empty($submenu)? 'simple' : 'complex';
            $tpl->SetBlock("$block/items");
            $tpl->SetBlock("$block/items/$innerBlock");

            $menu['url'] = $menu['url']?: 'javascript:void(0);';
            $tpl->SetVariable('level', $level);
            $tpl->SetVariable('mid', $menu['id']);
            $tpl->SetVariable('title', $menu['title']);
            $tpl->SetVariable('url', $menu['url']);
            $tpl->SetVariable('target', ($menu['target']==0)? '_self': '_blank');
            $tpl->SetVariable('symbol', $menu['symbol']);

            if (!empty($menu['image'])) {
                $src = $this->gadget->urlMap('LoadImage', array('id' => $menu['id']));
                $image =& Piwi::CreateWidget('Image', $src, $menu['title']);
                $image->SetID('');
                $tpl->SetVariable('image', $image->get());
            } else {
                $tpl->SetVariable('image', '');
            }

            //menu selected?
            $selected = str_replace(BASE_SCRIPT, '', urldecode($menu['url'])) == $this->_ReqURL;

            $className = '';
            if ($i == 0) {
                $className.= ' menu_first';
            }
            if ($i == $len - 1) {
                $className.= ' menu_last';
            }
            if ($selected) {
                $className.= ' active';
            }
            $tpl->SetVariable('class', trim($className));

            $tpl->SetVariable('submenu', $submenu);
            $tpl->ParseBlock("$block/items/$innerBlock");
            $tpl->ParseBlock("$block/items");
        }

        $tpl->ParseBlock("$block");
        return $tpl->Get();
    }

    /**
     * Displays/Parse a menu
     *
     * @access  public
     * @param   array   $menu   Menu attributes
     * @return  bool    True if parsed otherwise False
     */
    function ParseMenu(&$menu)
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
        // symbol
        if (empty($menu['symbol'])) {
            $menu['symbol'] = 'hide';
        }

        return true;
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