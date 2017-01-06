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
                'title' => _t('MENU_ACTIONS_MENU'),
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

        $this->_ReqURL = Jaws_Utils::getRequestURL();
        $this->_ReqURL = str_replace(BASE_SCRIPT, '', $this->_ReqURL);

        $tpl = $this->gadget->template->load('Menu.html', array('rawStore' => true));
        $tpl_str = $tpl->GetContent();
        $tpl->SetBlock('menu');
        $tpl->SetVariable('gid', $group['id']);
        $tpl->SetVariable('home', _t('MENU_HOME'));
        $tpl->SetVariable('menus_tree', $this->GetNextLevel($tpl_str, $group['id'], 0, -1));
        if ($group['title_view'] == 1) {
            $tpl->SetBlock("menu/group_title");
            $tpl->SetVariable('title', $group['title']);
            $tpl->ParseBlock("menu/group_title");
        }

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
        $logged = $GLOBALS['app']->Session->Logged();
        foreach ($menus as $i => $menu) {
            // is menu viewable?
            if ($menu['status'] == 0) {
                continue;
            }
            if ($menu['status'] != 1) {
                if ($logged xor $menu['status'] == Menu_Info::STATUS_LOGGED_IN) {
                    continue;
                }
            }

            // check default ACL
            if ($menu['type'] != 'url') {
                if (!Jaws_Gadget::IsGadgetInstalled($menu['type'])) {
                    continue;
                }

                if (!$GLOBALS['app']->Session->GetPermission($menu['type'], 'default')) {
                    continue;
                }

                // check permission
                if (!empty($menu['permission'])) {
                    $permission = unserialize($menu['permission']);
                    if (isset($permission['gadget'])) {
                        if (!$GLOBALS['app']->Session->GetPermission($permission['gadget'], 'default')) {
                            continue;
                        }
                    } else {
                        $permission['gadget'] = $menu['type'];
                    }

                    if (!$GLOBALS['app']->Session->GetPermission(
                        $permission['gadget'],
                        $permission['key'],
                        $permission['subkey']
                    )) {
                        continue;
                    }
                }
            }

            // replace menu variables
            if (!empty($menu['variables'])) {
                $objGadget = Jaws_Gadget::getInstance($menu['type']);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }

                $params = array();
                $vars = unserialize($menu['variables']);
                $url  = unserialize($menu['url']);
                foreach ($vars as $var => $val) {
                    $val = $GLOBALS['app']->Session->GetAttribute($val);
                    if (is_null($val)) {
                        continue 2;
                    }
                    // set url variables
                    foreach ($url['params'] as $param => $str) {
                        $params[$param] = Jaws_UTF8::str_replace('{' . $var . '}', $val, $str);
                    }
                    // set title variables
                    $menu['title'] = Jaws_UTF8::str_replace('{' . $var . '}', $val, $menu['title']);
                }

                // generate url map
                $menu['url'] = $objGadget->urlMap(
                    $url['action'],
                    $params,
                    false,
                    isset($url['gadget'])? $url['gadget'] : ''
                );
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
     * Returns menu image as stream data
     *
     * @access  public
     * @return  bool    True on successful, False otherwise
     */
    function LoadImage()
    {
        $id = (int)jaws()->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Menu');
        $image = $model->GetMenuImage($id);
        if (!Jaws_Error::IsError($image)) {
            $objImage = Jaws_Image::factory();
            if (!Jaws_Error::IsError($objImage)) {
                $objImage->setData($image, true);
                $res = $objImage->display('', null, 315360000);// cached for 10 years!
                if (!Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        return false;
    }
}