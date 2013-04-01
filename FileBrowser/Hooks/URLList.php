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
class FileBrowserURLListHook
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
        $urls = array();
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('FileBrowser', 'DefaultAction'),
                        'title' => _t('FILEBROWSER_NAME'));
        return $urls;
    }
}
