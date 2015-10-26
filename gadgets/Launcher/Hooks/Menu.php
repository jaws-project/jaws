<?php
/**
 * Launcher - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Launcher
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Launcher_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();
        $model = $this->gadget->model->load('Scripts');
        $scripts = $model->GetScripts();
        if (!Jaws_Error::isError($scripts)) {
            foreach ($scripts as $script) {
                $urls[] = array(
                    'url' => $this->gadget->urlMap('Execute', array('script' => $script)),
                    'title' => $script
                );
            }
        }

        return $urls;
    }
}