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
class Sitemap_Model_Ping extends Jaws_Gadget_Model
{
    /**
     * Pings the sitemap.xml file to many search engines
     *
     * @access  public
     * @param   bool    $redo   Rewrites sitemap before sending it(Optional)
     * @return  bool    True
     */
    function ping($redo = false)
    {
        if ($redo === true) {
            $model =  $this->gadget->model->load('Sitemap');
            $buildSitemap = $model->makeSitemap(true);
        }

        $url = htmlentities($this->gadget->urlMap('GetXML'), ENT_QUOTES, 'UTF-8');
        $sengines = array(
            'http://www.google.com/webmasters/sitemaps/ping?sitemap={local}' => 'get',
            'http://submissions.ask.com/ping?sitemap={local}' => 'get'
        );


        require_once PEAR_PATH. 'HTTP/Request.php';

        $httpRequest = new HTTP_Request();
        foreach($sengines as $engine => $method) {
            $method = strtolower($method);
            if ($method == 'post') {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
            } else {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
            }
            $engine      = str_replace('local', $url, $engine);

            $httpRequest->setURL($engine);
            $resRequest  = $httpRequest->sendRequest();

            if (PEAR::isError($resRequest) || (int) $httpRequest->getResponseCode() <> 200) {
                $GLOBALS['log']->Log(JAWS_LOG_INFO, 'Could not ping sitemap URL to: '.$engine);
            }
        }
        return true;
    }

}