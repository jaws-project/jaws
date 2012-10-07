<?php
/**
 * Launcher Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LauncherLayoutHTML
{
    /**
     * Loads layout actions
     *
     * @access private
     */
    function LoadLayoutActions()
    {
        $model    = $GLOBALS['app']->LoadGadget('Launcher', 'Model');
        $launcher = $model->GetLaunchers(true);
        $actions  = array();
        if (!Jaws_Error::isError($launcher)) {
            foreach ($launcher as $script) {
                $actions['Display(' . $script . ')'] = array(
                                                              'mode' => 'LayoutAction',
                                                              'name' => $script,
                                                              'desc' => _t('LAUNCHER_DISPLAY')
                                                              );
            }
        }
        return $actions;
    }

    /**
     * Show a Launcher
     *
     * @var string  $script Script name 
     * @access  public
     * @return  string  Script content
     */
    function Display($script)
    {
        $model   = $GLOBALS['app']->LoadGadget('Launcher', 'Model');
        $output  = $model->GetLauncher($script, null);
        if (Jaws_Error::isError($output)) {
            return '';
        }
        return $output;
    }
}
?>
