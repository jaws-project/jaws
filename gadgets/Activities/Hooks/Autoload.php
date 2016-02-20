<?php
/**
 * Activities Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    Activities
 */
class Activities_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload function
     *
     * @access  private
     * @return  void
     */
    function Execute()
    {
        $gadget = $this->gadget->action->load('Activities');
        return $gadget->PostData();
    }

}