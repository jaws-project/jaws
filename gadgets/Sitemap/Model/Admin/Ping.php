<?php
/**
 * Sitemap Gadget
 *
 * @category   GadgetModel
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Model_Admin_Ping extends Jaws_Gadget_Model
{
    /**
     * Ping search engines to announce sitemap.xml updated
     *
     * @access  public
     * @return  bool    True
     */
    function PingSearchEngines()
    {
        $url = $this->gadget->urlMap('SitemapXML', array(), true);
        $searchEngines = array(
            'google' => 'http://www.google.com/webmasters/tools/ping?sitemap={url}',
            'bing' => 'http://www.bing.com/ping?sitemap={url}',
        );

        $httpRequest = new Jaws_HTTPRequest();
        $httpRequest->default_error_level = JAWS_ERROR_NOTICE;
        foreach ($searchEngines as $engine => $pingURL) {
            $pingURL = str_replace('{url}', $url, $pingURL);
            $result = $httpRequest->get($pingURL, $retData);
            if (!Jaws_Error::IsError($result) && $result != 200) {
                $GLOBALS['log']->Log(JAWS_ERROR_NOTICE, "Could not ping search engine '$engine': $retData");
            }
        }

        return true;
    }

}