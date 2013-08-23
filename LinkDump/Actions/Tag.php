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
class LinkDump_Actions_Tag extends Jaws_Gadget_HTML
{
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

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Links');
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