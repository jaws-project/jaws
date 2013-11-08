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
class Sitemap_Actions_Breadcrumb extends Jaws_Gadget_Action
{
    /**
     * Builds bread crumb
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Breadcrumb()
    {
        $model = $this->gadget->model->load('Breadcrumb');
        $path = jaws()->request->fetch('path', 'get');
        $bc = $model->GetBreadcrumb($path);
        $tpl = $this->gadget->template->load('Breadcrumb.html');
        $tpl->SetBlock('sitemap_breadcrumb');
        $c = 1;
        $t = count($bc);
        foreach ($bc as $url => $title) {
            if ($c == $t) {
                $tpl->SetBlock('sitemap_breadcrumb/last');
                $tpl->SetVariable('url', $url);
                $tpl->SetVariable('title', $title);
                $tpl->ParseBlock('sitemap_breadcrumb/last');
            } else {
                $tpl->SetBlock('sitemap_breadcrumb/item');
                $tpl->SetVariable('url', $url);
                $tpl->SetVariable('title', $title);
                $tpl->ParseBlock('sitemap_breadcrumb/item');
            }
            $c++;
        }
        $tpl->ParseBlock('sitemap_breadcrumb');
        return $tpl->get();
    }

}