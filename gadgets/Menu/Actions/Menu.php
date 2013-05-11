<?php
/**
 * Menu Gadget
 *
 * @category   Gadget
 * @package    Menu
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Menu_Actions_Menu extends Jaws_Gadget_HTML
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
        $mModel = $GLOBALS['app']->LoadGadget('Menu', 'Model');
        $groups = $mModel->GetGroups();
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
        $model = $GLOBALS['app']->LoadGadget('Menu', 'Model');
        $group = $model->GetGroups($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !$group['published']) {
            return false;
        }

        $this->_ReqURL = Jaws_Utils::getRequestURL();
        $this->_ReqURL = str_replace(BASE_SCRIPT, '', $this->_ReqURL);

        $tpl = new Jaws_Template('gadgets/Menu/templates/');
        $tpl->Load('Menu.html', true);
        $tpl->SetBlock('levels');

        $tpl_str = $tpl->GetRawBlockContent();

        $tpl->SetBlock('menu');
        $tpl->SetVariable('gid', $group['id']);
        $tpl->SetVariable('menus_tree', $this->GetNextLevel($model, $tpl_str, $group['id'], 0));
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
     * @param   object  $model      Jaws_Model reference
     * @param   string  $tpl_str    XHTML template content passed by reference
     * @param   int     $gid        Group ID
     * @param   int     $pid
     * @return  string  XHTML template content with sub menu items
     */
    function GetNextLevel(&$model, &$tpl_str, $gid, $pid)
    {
        $menus = $model->GetLevelsMenus($pid, $gid, true);
        if (Jaws_Error::IsError($menus) || empty($menus)) return '';

        $tpl = new Jaws_Template();
        $tpl->LoadFromString($tpl_str);
        $tpl->SetBlock('levels');

        $len = count($menus);
        static $level = -1;
        for ($i = 0; $i < $len; $i++) {
            $level++;
            $tpl->SetVariable('level', $level);
            $tpl->SetBlock('levels/menu_item');
            $tpl->SetVariable('mid', $menus[$i]['id']);
            $tpl->SetVariable('title', $menus[$i]['title']);
            $tpl->SetVariable('url', $menus[$i]['url']);
            $tpl->SetVariable('target', ($menus[$i]['url_target']==0)? '_self': '_blank');

            if (!empty($menus[$i]['image'])) {
                $src = $GLOBALS['app']->Map->GetURLFor('Menu', 'LoadImage', array('id' => $menus[$i]['id']));
                $image =& Piwi::CreateWidget('Image', $src, $menus[$i]['title']);
                $image->SetID('');
                $tpl->SetVariable('image', $image->get());
            } else {
                $tpl->SetVariable('image', '');
            }

            //menu selected?
            $selected = str_replace(BASE_SCRIPT, '', urldecode($menus[$i]['url'])) == $this->_ReqURL;
            //get sub level menus
            $subLevel = $this->GetNextLevel($model, $tpl_str, $gid, $menus[$i]['id']);
            if ($selected || !empty($subLevel) || $i == 0 || $i == $len - 1) {
                $tpl->SetBlock('levels/menu_item/class');
                if ($i == 0) {
                    $tpl->SetBlock('levels/menu_item/class/first');
                    $tpl->ParseBlock('levels/menu_item/class/first');
                }
                if ($i == $len - 1) {
                    $tpl->SetBlock('levels/menu_item/class/last');
                    $tpl->ParseBlock('levels/menu_item/class/last');
                }
                if ($selected) {
                    $tpl->SetBlock('levels/menu_item/class/current');
                    $tpl->ParseBlock('levels/menu_item/class/current');
                }
                if (!empty($subLevel)) {
                    $tpl->SetBlock('levels/menu_item/class/super');
                    $tpl->ParseBlock('levels/menu_item/class/super');
                }
                $tpl->ParseBlock('levels/menu_item/class');
            }

            $tpl->SetVariable('sub_menu', $subLevel);
            $tpl->ParseBlock('levels/menu_item');
            $level--;
        }

        $tpl->ParseBlock('levels');
        return $tpl->Get();
    }

}