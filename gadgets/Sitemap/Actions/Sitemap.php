<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Sitemap extends Jaws_Gadget_Action
{
    /**
     * Sitemap XML content
     *
     * @access  public
     * @return  string  XML content
     */
    function SitemapXML()
    {
        header('Content-Type: text/xml; charset=utf-8');
        $sitemap = $this->gadget->model->load('Sitemap');

        $gadget = jaws()->request->fetch('gname', 'get');
        if (empty($gadget)) {
            return $sitemap->GetSitemapXML();
        } else {
            $xml = $sitemap->GetGadgetSitemapXML($gadget);
            if(empty($xml)) {
                return Jaws_HTTPError::Get(404);
            }
            return $xml;
        }

    }
}