<?php
/**
 * Settings Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    Settings
 */
class Settings_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload function
     *
     * @access  private
     * @return  void
     */
    function Execute()
    {
        if ($this->gadget->registry->fetch('pwa_enabled')) {
            $tpl = $this->gadget->template->load('ServiceWorker.js');
            $tpl->SetBlock('Registration');
            $tpl->ParseBlock('Registration');
            $GLOBALS['app']->Layout->addScript(array('text' => $tpl->Get()));
        }
    }

}