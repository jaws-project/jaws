<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_MenuNavigation
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;


    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
    {
        $this->gadget = $gadget;
        $this->app = Jaws::getInstance();
    }

    /**
     * Get menu navigation
     *
     * @access  public
     * @param   object  $tpl        (Optional) Jaws Template object
     * @param   array   $options    (Optional) Menu options
     * @param   string  $label      (Optional) Menu label
     * @return  string  XHTML template content
     */
    function navigation($tpl, $options = array(), $label = '')
    {
        if (empty($tpl)) {
            $tpl = new Jaws_Template();
            $tpl->Load('MenuNavigation.html', 'include/Jaws/Resources');
            $block = '';
        } else {
            $block = $tpl->GetCurrentBlockPath();
        }
        $tpl->SetBlock("$block/navigation");

        $tpl->SetVariable('gadget', $this->gadget->name);
        $tpl->SetVariable('label', empty($label)? Jaws::t('GADGET_ACTIONS_MENUS') : $label);

        if (empty($options)) {
            // use gadget normal actions if navigation index exist
            foreach ($this->gadget->actions['index'] as $actionName => $action) {
                if (!isset($action['normal']) || !$action['normal'] || !isset($action['navigation'])) {
                    continue;
                }

                $menu = array(
                    'name'  => $actionName,
                    'title' => _t(strtoupper("{$this->gadget->name}_ACTIONS_{$actionName}_TITLE")),
                    'url' => $this->gadget->urlMap(
                        $actionName,
                        isset($action['navigation']['params'])? $action['navigation']['params'] : array()
                    ),
                );
                // set active menu
                if ($this->app->mainRequest['gadget'] == $this->gadget->name &&
                    $this->app->mainRequest['action'] == $actionName
                ) {
                    $menu['active'] = true;
                }
                // check permissions
                if (isset($action['acls'])) {
                    if (!call_user_func_array(array($this->gadget, 'GetPermission'), $action['acls'])) {
                        continue;
                    }
                }
                // separator
                if (isset($action['navigation']['separator'])) {
                    $menu['separator'] = true;
                }
                // set order
                $order = isset($action['navigation']['order'])? $action['navigation']['order'] : null;
                if (isset($order)) {
                    $options[$order] = $menu;
                } else {
                    $options[] = $menu;
                }
            }

            ksort($options);
        }

        // put menus in template
        foreach ($options as $menu) {
            $tpl->SetBlock("$block/navigation/menu");
            // set separator
            if (isset($menu['separator']) && !empty($menu['separator'])) {
                $tpl->SetBlock("$block/navigation/menu/separator");
                $tpl->ParseBlock("$block/navigation/menu/separator");
            }
            // set active menu
            if (isset($menu['active']) && $menu['active']) {
                $tpl->SetBlock("$block/navigation/menu/active");
                $tpl->ParseBlock("$block/navigation/menu/active");
            }

            $tpl->SetVariable('title', $menu['title']);
            $tpl->SetVariable('url', $menu['url']);
            $tpl->ParseBlock("$block/navigation/menu");
        }

        $tpl->ParseBlock("$block/navigation");
        return $tpl->Get();
    }

    /**
     * Get menu navigation array
     *
     * @access  public
     * @param   array   $options    (Optional) Menu options
     * @param   string  $label      (Optional) Menu label
     * @return  array   Gadget navigation array
     */
    function xnavigation($options = array(), $label = '')
    {
        if (empty($options)) {
            // use gadget normal actions if navigation index exist
            foreach ($this->gadget->actions['index'] as $actionName => $action) {
                if (!isset($action['normal']) || !$action['normal'] || !isset($action['navigation'])) {
                    continue;
                }

                $menu = array(
                    'name'  => $actionName,
                    'title' => _t(strtoupper("{$this->gadget->name}_ACTIONS_{$actionName}_TITLE")),
                    'url' => $this->gadget->urlMap(
                        $actionName,
                        isset($action['navigation']['params'])? $action['navigation']['params'] : array()
                    ),
                );
                // set active menu
                if ($this->app->mainRequest['gadget'] == $this->gadget->name &&
                    $this->app->mainRequest['action'] == $actionName
                ) {
                    $menu['active'] = true;
                }
                // check permissions
                if (isset($action['acls'])) {
                    if (!call_user_func_array(array($this->gadget, 'GetPermission'), $action['acls'])) {
                        continue;
                    }
                }
                // separator
                if (isset($action['navigation']['separator'])) {
                    $menu['separator'] = true;
                }
                // set order
                $order = isset($action['navigation']['order'])? $action['navigation']['order'] : null;
                if (isset($order)) {
                    $options[$order] = $menu;
                } else {
                    $options[] = $menu;
                }
            }

            ksort($options);
        }

        return array(
            'gadget' => $this->gadget->name,
            'label'  => empty($label)? Jaws::t('GADGET_ACTIONS_MENUS') : $label,
            'menus'  => $options
        );
    }

}