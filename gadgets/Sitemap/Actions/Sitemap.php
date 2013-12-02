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
        $model = $this->gadget->model->load('Sitemap');

        $gadget = jaws()->request->fetch('gname', 'get');
        if (empty($gadget)) {
            return $model->GetSitemapXML();
        } else {
            $xml = $model->GetGadgetSitemapXML($gadget);
            if(empty($xml)) {
                return Jaws_HTTPError::Get(404);
            }
            return $xml;
        }
    }

    /**
     * Display sitemap
     *
     * @access  public
     * @return  xHTML  HTML Content
     */
    function Sitemap()
    {
        $tpl = $this->gadget->template->load('Sitemap.html');
        $tpl->SetBlock('sitemap');
        $tpl->SetVariable('title', _t('SITEMAP_SITEMAP'));

        $model = $this->gadget->model->load('Sitemap');
        $gadgets = $model->GetAvailableSitemapGadgets();
        foreach ($gadgets as $gadget) {
            $tpl->SetBlock('sitemap/item');
            $items = $model->GetSitemapData($gadget['name']);
            $tpl->SetVariable('title', $gadget['title']);
            $tpl->SetVariable('childs', $this->GetNextLevel($gadget['name'], $items));
            $tpl->ParseBlock('sitemap/item');
        }

        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }


    /**
     * Returns the HTML of a group of sitemap childs (sub levels)
     *
     * @access  private
     * @param   string  $gadget Gadget name
     * @param   array   $items  Nested item childs passed by reference
     * @return  string  XHTML of nested childs
     */
    function GetNextLevel($gadget, &$items)
    {
        $tpl = $this->gadget->template->load('Sitemap.html');

        if (count($items) > 0) {
            $tpl->SetBlock('branch');
            foreach ($items as $item) {
                $tpl->SetBlock('branch/item');
                $tpl->SetVariable('title', $item['title']);
                $tpl->SetVariable('url', $item['url']);
                $tpl->SetVariable('childs', $this->GetNextLevel($gadget, $item['childs']));
                $tpl->ParseBlock('branch/item');
            }
            $tpl->ParseBlock('branch');
        }
        return $tpl->Get();
    }

}