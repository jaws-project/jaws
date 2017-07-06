<?php
/**
 * Weather Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Actions_Admin_GoogleMap extends Jaws_Gadget_Action
{
    /**
     * Returns google map image
     *
     * @access  public
     * @return  void
     */
    function GetGoogleMapImage()
    {
        $gMapParams = $this->gadget->request->fetch(array('latitude', 'longitude', 'zoom', 'size'), 'get');

        $gMapURL = 'http://maps.google.com/maps/api/staticmap?center='.
            $gMapParams['latitude']. ',' . $gMapParams['longitude'].
            '&zoom='. $gMapParams['zoom']. '&size='. $gMapParams['size'].
            '&maptype=roadmap&markers=color:blue|label:x|'.
            $gMapParams['latitude']. ','. $gMapParams['longitude'].
            '&sensor=false';

        header("Content-Type: image/png");
        header("Pragma: public");

        $httpRequest = new Jaws_HTTPRequest();
        $result = $httpRequest->get($gMapURL, $data);
        if (Jaws_Error::IsError($result) || $result != 200) {
            $data = @file_get_contents('gadgets/Weather/Resources/images/gmap.png');
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        } else {
            $expires = 60*60*48;
            header("Cache-Control: max-age=".$expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        }

        echo $data;
    }
}