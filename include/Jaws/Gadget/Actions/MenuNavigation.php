<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_MenuNavigation
{
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
        $tpl->SetVariable('label', empty($label)? _t('GLOBAL_GADGET_ACTIONS_MENUS') : $label);

        foreach ($options as $menu) {
            if (isset($menu['visible']) && !$menu['visible']) {
                continue;
            }

            $tpl->SetBlock("$block/navigation/menu");
            if (isset($menu['separator'])) {
                $tpl->SetBlock("$block/navigation/menu/separator");
                $tpl->ParseBlock("$block/navigation/menu/separator");
            } else {
                $tpl->SetBlock("$block/navigation/menu/link");
                $tpl->SetVariable('title', $menu['title']);
                $tpl->SetVariable('url', $menu['url']);
                $tpl->ParseBlock("$block/navigation/menu/link");
            }
            $tpl->ParseBlock("$block/navigation/menu");
        }

        $tpl->ParseBlock("$block/navigation");
        return $tpl->Get();
    }

}