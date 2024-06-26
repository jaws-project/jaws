<?php
/**
 * Search - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Search
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Search_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all possible items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of menu items
     */
    function Execute()
    {
        $urls[] = array('url'    => $this->gadget->urlMap('Box'),
                        'title'  => $this::t('ACTIONS_BOX'),
                        'title2' => $this->gadget->title);
        $urls[] = array('url'    => $this->gadget->urlMap('SimpleBox'),
                        'title'  => $this::t('ACTIONS_SIMPLEBOX'),
                        'title2' => $this->gadget->title);
        $urls[] = array('url'    => $this->gadget->urlMap('AdvancedBox'),
                        'title'  => $this::t('ACTIONS_ADVANCEDBOX'),
                        'title2' => $this->gadget->title);
        return $urls;
    }
}
