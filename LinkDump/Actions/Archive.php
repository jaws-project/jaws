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
class LinkDump_Actions_Archive extends Jaws_Gadget_HTML
{
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

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
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
}