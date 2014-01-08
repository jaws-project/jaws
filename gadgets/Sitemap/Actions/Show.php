<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Show extends Jaws_Gadget_Action
{
    /**
     * Displays the Menu
     *
     * @access  public
     * @param   int     $levels Displays N levels, -1 displays without top, false returns all
     * @return  string  XHTML menu
     */
    function Show($levels = false)
    {
        $model = $this->gadget->model->load('Sitemap');
        $items = $model->GetItems($levels);

        $tpl = $this->gadget->template->load('Show.html');
        $tpl->SetBlock('branch');
        $tplString = $tpl->GetCurrentBlockContent();
        $tpl->SetBlock('branch/menu');
        $tplString = str_replace(
            '##menu##',
            '<!-- BEGIN menu -->'.$tpl->GetCurrentBlockContent().'<!-- END menu -->',
            $tplString);
        $tplString = '<!-- BEGIN branch -->' . $tplString . '<!-- END branch -->';

        $tpl->SetBlock('sitemap_show');
        $tpl->SetVariable('title', '');
        $tpl->SetVariable('menus_tree', $this->DisplayMenu($items, $tplString));
        $tpl->ParseBlock('sitemap_show');

        return $tpl->get();
    }

    /**
     * Displays the menu without top elements
     *
     * @access  public
     * @return  string  XHTML menu
     */
    function ShowWithoutTop()
    {
        return $this->Show(-1);
    }

    /**
     * Displays menu with the first two levels opened
     *
     * @access  public
     * @return  string  XHTML menu
     */
    function ShowTwoLevels()
    {
        return $this->Show(2);
    }

    /**
     * Displays menu with the first three levels opened
     *
     * @access  public
     * @return  string  XHTML menu
     */
    function ShowThreeLevels()
    {
        return $this->Show(3);
    }

    /**
     * Displays top menu
     *
     * @access  public
     * @return  string  XHTML top menu
     */
    function TopMenu()
    {
        $tpl = $this->gadget->template->load('TopMenu.html');
        $tpl->SetBlock('topmenu');
        $model = $this->gadget->model->load('Sitemap');

        if ($GLOBALS['app']->Layout->mainGadget == 'Sitemap') {
            $items = $model->GetItems(jaws()->request->fetch('path', 'get'));
        } else {
            $items = $model->GetItems(1);
        }
        foreach ($items as $item) {
            $tpl->SetBlock('topmenu/item');
            $tpl->SetVariable('url',$item['url']);
            $tpl->SetVariable('title',$item['title']);
            $tpl->ParseBlock('topmenu/item');
        }
        $tpl->ParseBlock('topmenu');
        return $tpl->Get();
    }

    function GetBranch($a, $find) {
        $aux = explode('/', $find);
        $c = count($aux);
        $s = '';
        for ($i = 0; $i < $c; $i++) {
            foreach ($a as $v) {
                if ($v['shortname'] == $aux[$i]) {
                    $a = $v['childs'];
                    if ($i+1 == $c) return $a;
                }
            }
        }
        return false;
    }

    /**
     * Internal recursive function to build the menu
     *
     * @access  private
     * @param   array   $items
     * @param   string  $tplString
     * @param   int     $level
     * @return  string  XHTML menu
     */
    function DisplayMenu(&$items, &$tplString, $level = 1) {
        $tpl = new Jaws_Template();
        $tpl->LoadFromString($tplString);
        $tpl->SetBlock('branch');
        $tpl->SetVariable('level', $level);
        if(count($items) > 0) {
            foreach ($items as $item) {
                $tpl->SetBlock('branch/menu');
                $tpl->SetVariable('level', $level);
                $tpl->SetVariable('url', $item['url']);
                $tpl->SetVariable('title', $item['title']);
                $active = '';
                if (($GLOBALS['app']->mainGadget == 'Sitemap') &&
                    (jaws()->request->fetch('path', 'get') == $item['path'])) {
                    $active = 'active';
                }
                $tpl->SetVariable('active', $active);
                if (count($item['childs']) > 0) {
                    $tpl->SetVariable('submenu', $this->DisplayMenu($item['childs'], $tplString, $level + 1));
                } else {
                    $tpl->SetVariable('submenu', '');
                }
                $tpl->ParseBlock('branch/menu');
            }
        }
        $tpl->ParseBlock('branch');

        return $tpl->Get();
    }

    /**
     * Displays given level
     *
     * @access  public
     * @param   int     $depth Depth(default 1)
     * @return  string  XHTML level menu
     */
    function DisplayLevel($depth = 1)
    {
        $model = $this->gadget->model->load('Sitemap');
        $path = jaws()->request->fetch('path', 'get');
        $aux = explode('/',$path);
        if (count($aux) > 1) array_pop($aux);
        $find = implode('/',$aux);
        if ($find == '') return '';
        $branch = $this->GetBranch($model->_items, $find);

        $tpl = $this->gadget->template->load('Show.html');
        $tpl->SetBlock('branch');
        $tplString = $tpl->GetCurrentBlockContent();
        $tpl->SetBlock('branch/menu');
        $tplString = str_replace(
            '##menu##',
            '<!-- BEGIN menu -->'.$tpl->GetCurrentBlockContent().'<!-- END menu -->',
            $tplString);
        $tplString = '<!-- BEGIN branch -->' . $tplString . '<!-- END branch -->';

        $tpl->SetBlock('sitemap_show');
        $tpl->SetVariable('title', '');
        $tpl->SetVariable('menus_tree', $this->DisplayMenu($branch, $tplString));
        $tpl->ParseBlock('sitemap_show');

        return $tpl->get();
    }

}