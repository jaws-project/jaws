<?php
/**
 * PrivateMessage - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array('url'   => $this->gadget->urlMap('PrivateMessage'),
                        'title' => _t('PRIVATEMESSAGE_ACTIONS_PRIVATEMESSAGE'));

        return $urls;
    }

}