<?php
/**
 * Filebrowser Gadget
 *
 * @category   Gadget
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default action to be run if none is defined.
     *
     * @access  public
     * @return  string   XHTML template content of Default action
     */
    function DefaultAction()
    {
        $gadget = $GLOBALS['app']->loadGadget('FileBrowser', 'HTML', 'File');
        return $gadget->Display();
    }

}