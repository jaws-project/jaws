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
     * Pings the sitemap.xml file to many search engines
     *
     * @access  public
     * @return  bool    True
     */
    function PingSearchEngines()
    {
        $url = $this->gadget->urlMap('SitemapXML', array(), true);
        $searchEngines = array(
            'http://www.google.com/webmasters/tools/ping?sitemap={url}' => 'get',
            'http://www.bing.com/ping?sitemap={url}' => 'get',
        );

        require_once PEAR_PATH . 'HTTP/Request.php';

        $httpRequest = new HTTP_Request();
        foreach ($searchEngines as $engine => $method) {
            $method = strtolower($method);
            if ($method == 'post') {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
            } else {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
            }
            $engine = str_replace('{url}', $url, $engine);

            $httpRequest->setURL($engine);
            $resRequest = $httpRequest->sendRequest();

            if (PEAR::isError($resRequest) || (int)$httpRequest->getResponseCode() <> 200) {
                $GLOBALS['log']->Log(JAWS_LOG_INFO, 'Could not ping sitemap URL to: ' . $engine);
            }
        }
        return true;
    }

}