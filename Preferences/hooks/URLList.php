<?php
/**
 * Preferences - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Preferences
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PreferencesURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Preferences', 'DefaultAction'),
                        'title' => _t('PREFERENCES_ACTION_TITLE'));
        return $urls;
    }
}
