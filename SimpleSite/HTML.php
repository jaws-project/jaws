<?php
/**
 * SimpleSite Gadget
 *
 * @category   Gadget
 * @package    SimpleSite
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SimpleSiteHTML extends Jaws_GadgetHTML
{
    /**
     * Default action
     *
     * @access  public
     * @return  string  XHTML result
     */
    function DefaultAction()
    {
        return $this->Sitemap();
    }
    
    /**
     * Returns the HTML of a group of sitemap childs (sub levels)
     *
     * @access  private
     * @param   mixed   $xss    XSS parser passed by reference
     * @param   array   $items  Nested item childs passed by reference
     * @return  string  XHTML of nested childs
     */
    function GetNextLevel(&$xss, &$items)
    {
        $tpl = new Jaws_Template('gadgets/SimpleSite/templates/');
        $tpl->Load('Sitemap.html');

        if (count($items) > 0) {
            $tpl->SetBlock('branch');
            foreach ($items as $item) {
                $tpl->SetBlock('branch/item');
                $tpl->SetVariable('title', $xss->filter($item['title']));
                $tpl->SetVariable('url', $item['reference']);
                $tpl->SetVariable('childs', $this->GetNextLevel($xss, $item['childs']));
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
        $model = $GLOBALS['app']->LoadGadget('SimpleSite', 'Model');
        
        $tpl = new Jaws_Template('gadgets/SimpleSite/templates/');
        $tpl->Load('Sitemap.html');
        $xss   = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $items = $model->GetItems();

        if (count($items) > 0) {
            $tpl->SetBlock('sitemap');
            $tpl->SetBlock('sitemap/title');
            $tpl->SetVariable('title', _t('SIMPLESITE_SITEMAP'));
            $tpl->ParseBlock('sitemap/title');
            foreach ($items as $item) {
                $tpl->SetBlock('sitemap/item');
                $tpl->SetVariable('title', $xss->filter($item['title']));
                $tpl->SetVariable('url', $item['reference']);
                $tpl->SetVariable('childs', $this->GetNextLevel($xss, $item['childs']));
                $tpl->ParseBlock('sitemap/item');
            }
            $tpl->ParseBlock('sitemap');
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
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
        $sitemap = $GLOBALS['app']->LoadGadget('SimpleSite', 'Model');
        $xml     = $sitemap->makeSitemap(false);
        return $xml;
    }

    /**
     * Displays content
     * 
     * @access  public
     * @return  string  XHTML result
     */
    function Display()
    {
        // Get content via 'path'
        $request =& Jaws_Request::getInstance();
        $model = $GLOBALS['app']->LoadGadget('SimpleSite', 'Model');
        return $model->GetContent($request->get('path', 'get'));
    }
}