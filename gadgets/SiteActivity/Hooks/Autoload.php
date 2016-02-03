<?php
/**
 * SiteActivity Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    SiteActivity
 */
class SiteActivity_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload function
     *
     * @access  private
     * @return  void
     */
    function Execute()
    {
        $gadget = $this->gadget->action->load('SiteActivity');
        return $gadget->PostData();
    }

}