<?php
/**
 * FileBrowser - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    FileBrowser
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Hooks_Menu extends Jaws_Gadget_Hook
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
        $urls[] = array('url'   => $this->gadget->urlMap('Display'),
                        'title' => _t('FILEBROWSER_NAME'));
        return $urls;
    }
}
