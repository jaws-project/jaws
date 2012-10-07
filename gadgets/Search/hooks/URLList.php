<?php
/**
 * Search - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Search
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SearchURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     */
    function Hook()
    {
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Search', 'Box'),
                        'title'  => _t('SEARCH_LAYOUT_BOX'),
                        'title2' => _t('SEARCH_NAME'));
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Search', 'SimpleBox'),
                        'title'  => _t('SEARCH_LAYOUT_SIMPLEBOX'),
                        'title2' => _t('SEARCH_NAME'));
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Search', 'AdvancedBox'),
                        'title'  => _t('SEARCH_LAYOUT_ADVANCEDBOX'),
                        'title2' => _t('SEARCH_NAME'));
        return $urls;
    }
}
