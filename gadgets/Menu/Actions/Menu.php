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
        $mModel = $this->gadget->model->load('Menu');
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
        $tpl->SetVariable('menus_tree', $this->GetNextLevel($mModel, $tpl_str, $group['id'], 0));
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
     * @param   object  $model      Jaws_Model object
     * @param   string  $tpl_str    XHTML template content passed by reference
     * @param   int     $gid        Group ID
     * @param   int     $pid
     * @return  string  XHTML template content with sub menu items
     */
    function GetNextLevel(&$model, &$tpl_str, $gid, $pid)
    {
        $menus = $model->GetLevelsMenus($pid, $gid, true);
        if (Jaws_Error::IsError($menus) || empty($menus)) {
            return '';
        }

        $tpl = new Jaws_Template();
        $tpl->LoadFromString($tpl_str);
        $block = empty($pid)? 'mainmenu' : 'submenu';
        $tpl->SetBlock("$block");

        $len = count($menus);
        static $level = -1;
        for ($i = 0; $i < $len; $i++) {
            // check default ACL
            if ($menus[$i]['menu_type'] != 'url') {
                if (!$GLOBALS['app']->Session->GetPermission($menus[$i]['menu_type'], 'default')) {
                    continue;
                }

                // check ACL
                if (!empty($menus[$i]['acl_key_name']) &&
                    !$GLOBALS['app']->Session->GetPermission(
                        $menus[$i]['menu_type'],
                        $menus[$i]['acl_key_name'],
                        $menus[$i]['acl_key_subkey']
                    )
                ) {
                    continue;
                }
            }

            // check variable menu
            if ($menus[$i]['variable']) {
                $objGadget = Jaws_Gadget::getInstance($menus[$i]['menu_type']);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }

                $params = array();
                $url = unserialize($menus[$i]['url']);
                foreach ($url['params'] as $param => $str) {
                    if (!preg_match_all('@\{([[:alnum:]]+)\}@iu', $str, $vars, PREG_SET_ORDER)) {
                        continue;
                    }

                    foreach ($vars as $var) {
                        $val = $objGadget->session->fetch($var[1]);
                        if (is_null($val)) {
                            continue 3;
                        }
                        $str = str_replace('{' . $var[1] . '}', $val, $str);
                    }
                    $params[$param] = $str;
                }

                $menus[$i]['url'] = $objGadget->urlMap($url['action'], $params);
            }

            $level++;
            //get sub level menus
            $subLevel = $this->GetNextLevel($model, $tpl_str, $gid, $menus[$i]['id']);
            $subBlock = empty($subLevel)? 'simple' : 'complex';
            $tpl->SetBlock("$block/$subBlock");

            $menus[$i]['url'] = $menus[$i]['url']?: 'javascript:void(0);';
            $tpl->SetVariable('level', $level);
            $tpl->SetVariable('mid', $menus[$i]['id']);
            $tpl->SetVariable('title', $menus[$i]['title']);
            $tpl->SetVariable('url', $menus[$i]['url']);
            $tpl->SetVariable('target', ($menus[$i]['url_target']==0)? '_self': '_blank');

            if (!empty($menus[$i]['image'])) {
                $src = $this->gadget->urlMap('LoadImage', array('id' => $menus[$i]['id']));
                $image =& Piwi::CreateWidget('Image', $src, $menus[$i]['title']);
                $image->SetID('');
                $tpl->SetVariable('image', $image->get());
            } else {
                $tpl->SetVariable('image', '');
            }

            //menu selected?
            $selected = str_replace(BASE_SCRIPT, '', urldecode($menus[$i]['url'])) == $this->_ReqURL;

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

            $tpl->SetVariable('submenu', $subLevel);
            $tpl->ParseBlock("$block/$subBlock");
            $level--;
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