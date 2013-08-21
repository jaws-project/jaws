<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_HTML extends Jaws_Gadget_HTML
{
    /**
     * Dafault action
     *
     * @access  public
     * @return  string  XHTML template content
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
     * @return  mixed  XHTML template content or false on error
     */
    function Archive()
    {
        $request =& Jaws_Request::getInstance();
        $gid = $request->get('id', 'get');
        $gid = Jaws_XSS::defilter($gid, true);

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || !isset($group['id'])) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('Archive.html');
        $tpl->SetBlock('archive');

        $tpl->SetVariable('gid',   $group['id']);
        $tpl->SetVariable('title', _t('LINKDUMP_LINKS_ARCHIVE'));
        $tpl->SetVariable('name', $group['title']);

        $group_id = empty($group['fast_url']) ?
            $group['id'] : $group['fast_url'];

        $tpl->SetVariable('feed', _t('LINKDUMP_LINKS_FEED'));
        $tpl->SetVariable('linkdump_rss', $this->gadget->urlMap('RSS', array('id' => $group_id)));

        $target = $this->gadget->registry->fetch('links_target');
        $target = ($target == 'blank') ? '_blank' : '_self';
        $block = ($group['link_type'] == 0) ? 'list' : 'link';

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
     * @return  string  XHTML template content
     */
    function Group()
    {
        return $this->Archive();
    }

    /**
     * Redirect to the URL and increase the clicks by one
     * 
     * @access  public
     */
    function Link()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model');

        $request =& Jaws_Request::getInstance();
        $lid = $request->get('id', 'get');
        $lid = Jaws_XSS::defilter($lid, true);

        $link = $model->GetLink($lid);
        if (!Jaws_Error::IsError($link) && !empty($link)) {
            $click = $model->Click($link['id']);
            if (!Jaws_Error::IsError($click)) {
                header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 301 Moved Permanently");
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
     * @return  string  XHTML compliant date
     */
    function Tag()
    {
        $request =& Jaws_Request::getInstance();
        $tag = $request->get('tag', 'get');
        $tag = Jaws_XSS::defilter($tag, true);

        $target = $this->gadget->registry->fetch('links_target');
        $target = ($target == 'blank')? '_blank' : '_self';

        $tpl = $this->gadget->loadTemplate('Tag.html');
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