<?php
/**
 * Browse your files on the server and insert file links into the content editor
 *
 * @category    Plugin
 * @package     FileExplorer
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2012-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileExplorer_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = "0.1.0";

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $url = BASE_SCRIPT. '?reqGadget=FileBrowser&amp;reqAction=BrowseFile';
        $popbutton =& Piwi::CreateWidget('Button',
                                         'popbutton',
                                         '',
                                         'plugins/FileExplorer/images/file-explorer.png');
        $popbutton->SetTitle($this->plugin::t('BROWSE_SERVER'));
        $popbutton->AddEvent(ON_CLICK, "browse('$textarea', '$url')");
        $popbutton->AddFile('plugins/FileExplorer/Resources/file-explorer.js');

        return $popbutton;
    }

}