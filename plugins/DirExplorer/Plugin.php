<?php
/**
 * Browse media files on the server and insert in to the content editor
 *
 * @category    Plugin
 * @package     DirExplorer
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DirExplorer_Plugin extends Jaws_Plugin
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
        $url = BASE_SCRIPT. '?reqGadget=Directory&amp;reqAction=Browse';
        $popbutton =& Piwi::CreateWidget('Button',
                                         'popbutton',
                                         '',
                                         'plugins/DirExplorer/images/media.png');
        $popbutton->SetTitle($this->plugin::t('BROWSE_SERVER'));
        $popbutton->AddEvent(ON_CLICK, "browse('$textarea', '$url')");
        $popbutton->AddFile('plugins/DirExplorer/Resources/dir-explorer.js');

        return $popbutton;
    }

}