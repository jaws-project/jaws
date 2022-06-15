<?php
/**
 * By PhooInsert you can browse and insert photos
 * from Phoo gadget into the content editor
 *
 * @category   Plugin
 * @package    PhooInsert
 * @author     Jose Francisco Garcia Martinez <jfgarcia.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PhooInsert_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = "0.6.3";

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $url = BASE_SCRIPT. '?reqGadget=Phoo&amp;reqAction=BrowsePhoo';

        $popbutton =& Piwi::CreateWidget('Button', 'popbutton', '', 'plugins/PhooInsert/images/image.png');
        $popbutton->SetTitle($this->plugin::t('INSERT_IMAGE'));
        $popbutton->AddEvent(ON_CLICK, "browsePhoo('$textarea', '$url')");
        $popbutton->AddFile('plugins/PhooInsert/Resources/PhooInsert.js');

        return $popbutton;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
    {
        if (Jaws_Gadget::IsGadgetInstalled ('Phoo')) {
            $howMany = preg_match_all("#\[phoo album=\"(.*?)\" picture=\"(.*?)\" title=\"(.*?)\" class=\"(.*?)\" size=\"(.*?)\" linked=\"(.*?)\"\]#si", $html, $matches);
            $new_html = $html;
            $url = $this->app->getSiteURL();
            $objPhoo = Jaws_Gadget::getInstance('Phoo')->model->load('Photos');
            for ($i = 0; $i < $howMany; $i++) {
                $albumid = $matches[1][$i];
                $imageid = $matches[2][$i];
                $title   = $matches[3][$i];
                $clase   = $matches[4][$i];
                $size    = $matches[5][$i];
                $linked  = $matches[6][$i];
                $image = $objPhoo->GetImageEntry($imageid);
                if (!Jaws_Error::IsError($image) && !empty($image)) {
                    if (strtoupper($size)=='THUMB') {
                        $img_file = ROOT_DATA_PATH . 'phoo/' . $image['thumb'];
                        $img_url  = $this->app->getDataURL('phoo/' . $image['thumb']);
                    } elseif (strtoupper($size)=='MEDIUM') {
                        $img_file = ROOT_DATA_PATH . 'phoo/' . $image['medium'];
                        $img_url  = $this->app->getDataURL('phoo/' . $image['medium']);
                    } else {
                        $img_file = ROOT_DATA_PATH . 'phoo/' . $image['image'];
                        $img_url  = $this->app->getDataURL('phoo/' . $image['image']);
                    }
                    $imgData = Jaws_Image::getimagesize($img_file);

                    if (strtoupper($linked) == 'YES' ){
                        $img_lnk = $this->app->map->GetMappedURL('Phoo',
                                                                   'ViewImage',
                                                                   array('id' => $imageid, 'albumid' => $albumid));
                        $new_text = '<a href="'.$img_lnk.'" ><img src="'. $img_url.'" title="'.
                                    $title.'"  alt="'. $title.'" class="'.$clase.'" height="'.
                                    $imgData['height'].'" width="'.$imgData['width'].'"/></a>' ;
                    } else {
                        $new_text = '<img src="'.$img_url.'" title="'. $title.'" alt="'. $title.'" class="'.
                                    $clase.'" height="'.$imgData['height'].'" width="'.$imgData['width'].'" />';
                    }
                    $textToReplace = "#\[phoo album=\"".$albumid."\" picture=\"".$imageid."\" title=\"".
                                     $title."\" class=\"".$clase."\" size=\"".$size."\" linked=\"".$linked."\"\]#";
                    $new_html = preg_replace ($textToReplace, $new_text, $new_html);
                }
            }
            return $new_html;
        }

        return $html;
    }

}