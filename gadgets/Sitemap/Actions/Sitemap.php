<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Sitemap extends Jaws_Gadget_Action
{
    /**
     * Displays content
     *
     * @access  public
     * @return  string  XHTML result
     */
    function Display()
    {
        // Get content via 'path'
        $model = $this->gadget->loadModel('Sitemap');
        return $model->GetContent(jaws()->request->fetch('path', 'get'));
    }

    /**
     * Returns the HTML of a group of sitemap childs (sub levels)
     *
     * @access  private
     * @param   array   $items  Nested item childs passed by reference
     * @return  string  XHTML of nested childs
     */
    function GetNextLevel(&$items)
    {
        $tpl = $this->gadget->loadTemplate('Sitemap.html');

        if (count($items) > 0) {
            $tpl->SetBlock('branch');
            foreach ($items as $item) {
                $tpl->SetBlock('branch/item');
                $tpl->SetVariable('title', $item['title']);
                $tpl->SetVariable('url', $item['reference']);
                $tpl->SetVariable('childs', $this->GetNextLevel($item['childs']));
                $tpl->ParseBlock('branch/item');
            }
            $tpl->ParseBlock('branch');
        }
        return $tpl->Get();
    }

    /**
     * Returns a string(based on a template) with a simple sitemap layout
     *
     * @access  public
     * @return  string  XHTML result
     */
    function Sitemap()
    {
        $model = $this->gadget->loadModel('Sitemap');

        $tpl = $this->gadget->loadTemplate('Sitemap.html');
        $items = $model->GetItems();
        if (count($items) > 0) {
            $tpl->SetBlock('sitemap');
            $tpl->SetBlock('sitemap/title');
            $tpl->SetVariable('title', _t('SITEMAP_SITEMAP'));
            $tpl->ParseBlock('sitemap/title');
            foreach ($items as $item) {
                $tpl->SetBlock('sitemap/item');
                $tpl->SetVariable('title', $item['title']);
                $tpl->SetVariable('url', $item['reference']);
                $tpl->SetVariable('childs', $this->GetNextLevel($item['childs']));
                $tpl->ParseBlock('sitemap/item');
            }
            $tpl->ParseBlock('sitemap');
        } else {
            return Jaws_HTTPError::Get(404);
        }
        return $tpl->Get();
    }

    /**
     * Prints the sitemap XML content
     *
     * @access  public
     * @return  string  XML content
     */
    function SitemapXML()
    {
        header('Content-Type: text/xml; charset=utf-8');
        $sitemap = $this->gadget->loadModel('Sitemap');
        $xml     = $sitemap->makeSitemap(false);
        return $xml;
    }


}