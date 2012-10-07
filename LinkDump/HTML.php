<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDumpHTML extends Jaws_GadgetHTML
{
    /**
     * Dafault action
     *
     * @access  public
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('LinkDump', 'LayoutHTML');
        return $layoutGadget->ShowCategories();
    }

    /**
     * Show the archives of links
     *
     * @access  public
     * @return  template content
     */
    function Archive()
    {
        $request =& Jaws_Request::getInstance();
        $gid = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $gid = $xss->defilter($gid, true);

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || !isset($group['id'])) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('Archive.html');
        $tpl->SetBlock('archive');

        $tpl->SetVariable('gid',   $group['id']);
        $tpl->SetVariable('title', _t('LINKDUMP_LINKS_ARCHIVE'));
        $tpl->SetVariable('name',  $group['title']);

        $feedname = empty($group['fast_url']) ?
                    $GLOBALS['app']->UTF8->str_replace(' ', '-', $group['title']) : $group['fast_url'];
        $feedname = preg_replace('/[@?^=%&:;\/~\+# ]/i', '\1', $feedname);

        $tpl->SetVariable('feed', _t('LINKDUMP_LINKS_FEED'));
        $tpl->SetVariable('linkdump_rdf', $GLOBALS['app']->getDataURL("xml/linkdump.$feedname.rdf", false));

        $target = $GLOBALS['app']->Registry->Get('/gadgets/LinkDump/links_target');
        $target = ($target == 'blank')? '_blank' : '_self';
        $block  = ($group['link_type']==0)? 'list' : 'link';

        $links = $model->GetGroupLinks($group['id'], null, $group['order_type']);
        if (!Jaws_Error::IsError($links)) {
            foreach ($links as $link) {
                $tpl->SetBlock("archive/$block");
                $tpl->SetVariable('target',      $target);
                $tpl->SetVariable('title',       $link['title']);
                $tpl->SetVariable('description', $link['description']);
                $tpl->SetVariable('url',         $link['url']);
                $tpl->SetVariable('clicks',      $link['clicks']);
                $tpl->SetVariable('lbl_clicks',  _t('LINKDUMP_LINKS_CLICKS'));
                if ($group['link_type'] == 2) {
                    $lid = empty($link['fast_url']) ? $link['id'] : $link['fast_url'];
                    $tpl->SetVariable('visit_url', $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Link', array('id' => $lid)));
                } else {
                    $tpl->SetVariable('visit_url', $link['url']);
                }
                $tpl->ParseBlock("archive/$block");
            }
        }

        $tpl->ParseBlock('archive');
        return $tpl->Get();
    }

    /**
     * Show the archives of links
     *
     * @access  public
     * @return  template content
     */
    function Group()
    {
        return $this->Archive();
    }

    /**
     * Populating RDF feed
     *
     * @access  public
     * @return  string  RDF
     */
    function PopulateFeed($gid, $limit = 10)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $links = $model->GetGroupLinks($gid, $limit);
        if (Jaws_Error::IsError($links)) {
            return false;
        }

        $url    = $GLOBALS['app']->GetSiteURL('/');
        $title  = $GLOBALS['app']->Registry->Get('/config/site_name');
        $desc   = $GLOBALS['app']->Registry->Get('/config/site_description');
        $author = $GLOBALS['app']->Registry->Get('/config/site_author');

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('Rdf.html');
        $tpl->SetBlock('RDF');
        $tpl->SetVariable('link', $url);
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('desc', $desc);

        foreach ($links as $link) {
            $tpl->SetBlock('RDF/RdfSeq');
            $tpl->SetVariable('rdf-seq-url', $link['url']);
            $tpl->ParseBlock('RDF/RdfSeq');
        }

        foreach ($links as $link) {
            $tpl->SetBlock('RDF/item');
            $tpl->SetVariable('item-link',      $link['url']);
            $tpl->SetVariable('item-title',     $link['title']);
            $tpl->SetVariable('item-creator',   $author);
            $tpl->SetVariable('item-date',      $link['updatetime']);
            $tpl->ParseBlock('RDF/item');
        }

        $tpl->ParseBlock('RDF');
        return $tpl->Get();
    }

    /**
     * Redirect to the URL and increase the clicks by one
     * @access  public
     */
    function Link()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');

        $request =& Jaws_Request::getInstance();
        $lid = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $lid = $xss->defilter($lid, true);

        $link = $model->GetLink($lid);
        if (!Jaws_Error::IsError($link)) {
            $click = $model->Click($link['id']);
            if (!Jaws_Error::IsError($click)) {
                header($xss->filter($_SERVER['SERVER_PROTOCOL'])." 301 Moved Permanently");
                Jaws_Header::Location($link['url']);
            }
        }

        // By default, on the errors stay in the main page
        Jaws_Header::Referrer();
    }

    /**
     * Generates an Archive for a specified tag
     *
     * @access  public
     * @return  XHTML compliant date
     */
    function Tag()
    {
        $request =& Jaws_Request::getInstance();
        $tag = $request->get('tag', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $tag = $xss->defilter($tag, true);

        $target = $GLOBALS['app']->Registry->Get('/gadgets/LinkDump/links_target');
        $target = ($target == 'blank')? '_blank' : '_self';

        $tpl = new Jaws_Template('gadgets/LinkDump/templates/');
        $tpl->Load('Tag.html');
        $tpl->SetBlock('tag');

        $tpl->SetVariable('title', _t('LINKDUMP_LINKS_TAG_ARCHIVE', $tag));
        $this->SetTitle(_t('LINKDUMP_LINKS_TAG_ARCHIVE', $tag));
        $tpl->SetVariable('linkdump_rdf', $GLOBALS['app']->getDataURL('xml/linkdump.rdf', false));

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $links = $model->GetTagLinks($tag);
        if (!Jaws_Error::IsError($links)) {
            foreach ($links as $link) {
                $tpl->SetBlock('tag/link');
                $tpl->SetVariable('target',      $target);
                $tpl->SetVariable('title',       $link['title']);
                $tpl->SetVariable('description', $link['description']);
                $tpl->SetVariable('url',         $link['url']);
                $tpl->SetVariable('clicks',      $link['clicks']);
                $tpl->SetVariable('lbl_clicks',  _t('LINKDUMP_LINKS_CLICKS'));
                $lid = empty($link['fast_url'])? $link['id'] : $link['fast_url'];
                $tpl->SetVariable('visit_url', $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Link', array('id' => $lid)));
                $tpl->ParseBlock('tag/link');
            }
        }

        $tpl->ParseBlock('tag');
        return $tpl->Get();
    }
}