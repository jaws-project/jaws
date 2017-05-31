<?php
/**
 * Gets album cover from Amazon.com
 *
 * @category   Plugin
 * @package    AlbumCover
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Amir Mohammad Saied <amir@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AlbumCover_Plugin
{
    var $friendly = true;
    var $version = '0.4';
    var $_AccessKey = 'A';

    /**
     * Installs the plugin
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        $new_dir = JAWS_DATA . 'AlbumCover' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), $this->_Name);
        }

        // Registry key
        $GLOBALS['app']->Registry->insert('devtag', 'MY DEV TAG', false, 'AlbumCover');

        return true;
    }

    /**
     * Uninstalls the plugin
     *
     * @access  public
     * @return  bool    True
     */
    function Uninstall()
    {
        Jaws_Utils::delete(JAWS_DATA . 'AlbumCover' . DIRECTORY_SEPARATOR);
        return true;
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
        $button =& Piwi::CreateWidget('Button', 'addalbumcover', '',
                        $GLOBALS['app']->getSiteURL('/plugins/AlbumCover/images/stock-album.png', true));
        $button->SetTitle(_t('PLUGINS_ALBUMCOVER_ADD').' ALT+A');
        $button->AddEvent(ON_CLICK, "javascript: insertTags('$textarea','[AlbumCover Artist=\'\' Album=\'\']','','');");
        $button->SetAccessKey('A');
        return $button;
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
        $AlbumPattern = '@\[AlbumCover\s+Artist=\'(.*?)\'\s+Album=\'(.*?)\'\]@sm';
        $new_html = preg_replace_callback($AlbumPattern, array(&$this, 'GetAlbumCover'), $html);
        return $new_html;
    }

    /**
     * Search callback for the album
     *
     * @access  public
     * @param   array   $data   Album data(artist and album)
     * @return  string  XHTML album image
     */
    function GetAlbumCover($data)
    {
        $albumDir = JAWS_DATA . 'AlbumCover/';

        if (!isset($data[1]) || !isset($data[2]) || empty($data[1]) || empty($data[2])) {
            return '';
        }

        $Artist = $data[1];
        $Album  = $data[2];
        $img = strtolower(str_replace(' ', '', $Artist)). '_' .strtolower(str_replace(' ', '', $Album)).'.jpg';

        ///FIXME needs error checking
        if (!$rs = is_file($albumDir.$img)) {
            $amazonImg = $this->GetAlbumCoverFromAmazon($Artist, $Album);
            if (empty($amazonImg)) {
                $img = 'images/unknown.png';
            } elseif (!@copy($amazonImg, $albumDir.$img)) {
                //FIXME: Notify that can't copy image to cache...
                $img = Jaws_XSS::filter($amazonImg);
            } else {
                $img = JAWS_DATA . 'AlbumCover/' . $img;
            }
        } else {
            $img = JAWS_DATA . 'AlbumCover/' . $img;
        }

        $text = $Artist . ' - ' . $Album;
        return '<img src="' . $img . '" alt="' . $text . '" title="' . $text . '" />';
    }

    /**
     * Convets the givven string to lower case and eliminates spaces
     *
     * @access  public
     * @param   string  $string     The raw string
     * @return  string  Parsed string
     */
    function ToLowerWithoutSpaces($string)
    {
        $string = strtolower($string);
        $string = str_replace(' ', '', $string);

        return $string;
    }

    /**
     * Searches for the album cover in Amazon
     *
     * @access  public
     * @param   string  $Artist Artist to search for
     * @param   string  $Album  Album to search for
     * @return  string  Name of the album image
     */
    function GetAlbumCoverFromAmazon($Artist, $Album)
    {
        $wsdl = 'http://soap.amazon.com/schemas3/AmazonWebServices.wsdl';
        require_once PEAR_PATH. 'SOAP/Client.php';
        $service = new SOAP_WSDL($wsdl);
        $page=1;
        $proxy = $service->getProxy();

        $devtag = $GLOBALS['app']->Registry->fetch('devtag', 'AlbumCover');

        $params = array(
            'artist'    => htmlentities($Artist),
            'keywords'  => htmlentities($Album),
            'mode'      => 'music',
            'page'      => $page,
            'tag'       => 'webservices-20',
            'devtag'    => $devtag,
            'type'      => 'lite'
        );

        $result = $proxy->ArtistSearchRequest($params);
        $pages  = isset($result->TotalPages) ? $result->TotalPages : 0;

        $bestMatch = '';
        $lowerArtist = $this->ToLowerWithoutSpaces($Artist);
        $lowerAlbum  = $this->ToLowerWithoutSpaces($Album);
        while ($page <= $pages) {
            foreach ($result->Details as $r) {
                if ($this->ToLowerWithoutSpaces($r->ProductName) == $lowerAlbum) {
                    foreach ($r->Artists as $a) {
                        if ($this->ToLowerWithoutSpaces($a) == $lowerArtist) {
                            $bestMatch = $r->ImageUrlMedium;
                            break 3;
                        }
                    }
                }
            }
            $params['page'] = $page++;
            $result = $proxy->ArtistSearchRequest($params);
        }

        return $bestMatch;
    }

}