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
        $gadget = Jaws_Gadget::filter($gadget);
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

        $defaultStatus      = Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_BOTH;

        $model = $this->gadget->model->load('Sitemap');
        $gadgets = $model->GetAvailableSitemapGadgets();
        foreach ($gadgets as $gadget) {
            $items = $model->GetSitemapData($gadget['name']);

            // Fetch gadget sitemap config
            $gadgetProperties = $model->GetGadgetProperties($gadget);
            $gadgetStatus = null;
            if (!empty($gadgetProperties)) {
                if(isset($gadgetProperties['status'])) {
                    $gadgetStatus = $gadgetProperties['status'];
                }
            }

            $allCategories = $items;
            $gadgetCategory = $model->GetGadgetCategoryProperties($gadget['name']);
            if(empty($allCategories) || count($allCategories)<1) {
                continue;
            }
            // Detect all gadget's categories status
            foreach($allCategories as $cat) {
                $status = null;
                if (isset($gadgetCategory[$cat['id']]['status'])) {
                    $status = $gadgetCategory[$cat['id']]['status'];
                }
                if (empty($status)) {
                    $status = $gadgetStatus;
                }
                if (empty($status)) {
                    $status = $defaultStatus;
                }
                $finalCategory[$cat['id']]['status'] = $status;
            }

            $tpl->SetBlock('sitemap/item');

            $tpl->SetVariable('title', $gadget['title']);
            $tpl->SetVariable('childs', $this->GetNextLevel($gadget['name'], $finalCategory, $items));
            $tpl->ParseBlock('sitemap/item');
        }

        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }


    /**
     * Returns the HTML of a group of sitemap childs (sub levels)
     *
     * @access  private
     * @param   string  $gadget         Gadget name
     * @param   array   $itemsStatus    Items status
     * @param   array   $items          Nested item childs passed by reference
     * @return  string  XHTML of nested childs
     */
    function GetNextLevel($gadget, $itemsStatus, &$items)
    {
        $tpl = $this->gadget->template->load('Sitemap.html');

        if (count($items) > 0) {
            $tpl->SetBlock('branch');
            foreach ($items as $item) {
                // check item display status
                if ($itemsStatus[$item['id']]['status'] != Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_BOTH &&
                    $itemsStatus[$item['id']]['status'] != Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_USER_SIDE ) {
                    continue;
                }

                $tpl->SetBlock('branch/item');
                $tpl->SetVariable('title', $item['title']);
                $tpl->SetVariable('url', $item['url']);
                $tpl->SetVariable('childs', $this->GetNextLevel($gadget, $itemsStatus, $item['childs']));
                $tpl->ParseBlock('branch/item');
            }
            $tpl->ParseBlock('branch');
        }
        return $tpl->Get();
    }

}