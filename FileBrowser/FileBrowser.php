<?php
/**
 * Browse your files on the server and insert file links into the content editor
 *
 * @category   Plugin
 * @package    FileBrowser
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser extends Jaws_Plugin 
{
    /**
     * Main Constructor
     *
     * @access   public
     * @return  void
     */
    function FileBrowser()
    {
        $this->_Name = "FileBrowser";
        $this->_Description = _t("PLUGINS_FILEBROWSER_DESCRIPTION");
        $this->_Example = '';
        $this->_IsFriendly = true;
        $this->_Version = "0.1.0";
    }

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $url = BASE_SCRIPT. '?gadget=FileBrowser&amp;action=BrowseFile';
        $popbutton =& Piwi::CreateWidget('Button',
                                         'popbutton',
                                         '',
                                         'plugins/FileBrowser/images/file-browser.png');
        $popbutton->SetTitle(_t('PLUGINS_FILEBROWSER_BROWSE_SERVER'));
        $popbutton->AddEvent(ON_CLICK, "browse('$textarea', '$url')");
        $popbutton->AddFile('plugins/FileBrowser/resources/FileBrowser.js');

        return $popbutton;
    }

}