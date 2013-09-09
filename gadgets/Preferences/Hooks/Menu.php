<?php
/**
 * Preferences - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Preferences
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   list of URLs
     */
    function Execute()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Preferences', 'Display'),
                        'title' => _t('PREFERENCES_ACTION_TITLE'));
        return $urls;
    }
}
