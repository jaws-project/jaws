<?php
/**
 * Directory - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url' => $this->gadget->urlMap('Directory'),
                        'title' => $this->gadget->title);
        return $urls;
    }
}
