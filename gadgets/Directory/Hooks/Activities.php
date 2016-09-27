<?php
/**
 * Directory - Activities hook
 *
 * @category    GadgetHook
 * @package     Directory
 */
class Directory_Hooks_Activities extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of Site activity
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $items = array();
        $items['File'] = _t('DIRECTORY_ACTIVITIES_ACTION_FILE');
        $items['Folder'] = _t('DIRECTORY_ACTIVITIES_ACTION_FOLDER');

        return $items;
    }

}