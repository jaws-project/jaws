<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Feeds extends Blog_Actions_Default
{
    /**
     * Displays or writes a RSS feed for the blog
     *
     * @access  public
     * @param   bool    $save   true to save RSS, false to display
     * @return  string  xml with RSS feed on display mode, nothing otherwise
     */
    function RSS($save = false)
    {
        header('Content-type: application/rss+xml; charset=utf-8');
        $model = $this->gadget->model->load('Feeds');
        $rss = $model->MakeRSS($save);
        if (Jaws_Error::IsError($rss) && !$save) {
            return '';
        }

        return $rss;
    }

    /**
     * Displays or writes an Atom feed for the blog
     *
     * @access  public
     * @param   bool    $save   true to save Atom, false to display
     * @return  string  xml with Atom feed on display mode, nothing otherwise
     */
    function Atom($save = false)
    {
        header('Content-type: application/atom+xml; charset=utf-8');
        $model = $this->gadget->model->load('Feeds');
        $atom = $model->MakeAtom($save);
        if (Jaws_Error::IsError($atom) && !$save) {
            return '';
        }

        return $atom;
    }

    /**
     * Displays a RSS feed for a given blog category
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function ShowRSSCategory()
    {
        header('Content-type: application/rss+xml; charset=utf-8');
        $id = jaws()->request->fetch('id', 'get');
        $id = Jaws_XSS::defilter($id);

        $model = $this->gadget->model->load('Feeds');
        $xml = $model->MakeCategoryRSS($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for a given blog category
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function ShowAtomCategory()
    {
        header('Content-type: application/atom+xml; charset=utf-8');
        $id = jaws()->request->fetch('id', 'get');
        $id = Jaws_XSS::defilter($id);

        $model = $this->gadget->model->load('Feeds');
        $xml = $model->MakeCategoryAtom($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Get then FeedsLink action params
     *
     * @access  public
     * @return  array list of the Banners action params
     */
    function FeedsLinkLayoutParams()
    {
        $result = array();
        $result[] = array(
            'title' => _t('BLOG_FEEDS_TYPE'),
            'value' => array(
                'RSS' => _t('BLOG_FEEDS_RSS') ,
                'Atom' => _t('BLOG_FEEDS_ATOM') ,
            )
        );
        return $result;
    }

    /**
     * Displays a link to blog feed
     *
     * @access  public
     * @param   string  $linkType (RSS | Atom)
     * @return  string  XHTML template content
     */
    function FeedsLink($linkType)
    {
        $tpl = $this->gadget->template->load('XMLLinks.html');
        if ($linkType == 'RSS') {
            $tpl->SetBlock('rss_link');
            $tpl->SetVariable('url', $this->gadget->urlMap('RSS'));
            $tpl->ParseBlock('rss_link');
        } else if ($linkType == 'Atom') {
            $tpl->SetBlock('atom_link');
            $tpl->SetVariable('url', $this->gadget->urlMap('Atom'));
            $tpl->ParseBlock('atom_link');
        }
        return $tpl->Get();
    }

}