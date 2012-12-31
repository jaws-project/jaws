<?php
/**
 * Launcher - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Launcher
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LauncherURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Hook()
    {
        $urls  = array();
        $model = $GLOBALS['app']->LoadGadget('Launcher', 'Model');
        $scripts = $model->GetScripts();
        if (!Jaws_Error::isError($scripts)) {
            foreach ($scripts as $script) {
                $urls[] = array(
                    'url' => $GLOBALS['app']->Map->GetURLFor(
                        'Launcher',
                        'Execute',
                        array('script' => $script)
                    ),
                    'title' => $script
                );
            }
        }

        return $urls;
    }

}