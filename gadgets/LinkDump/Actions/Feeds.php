<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Feeds extends Jaws_Gadget_Action
{
    /**
     * Displays or writes a RDF feed for the link group
     *
     * @access  public
     * @return  string  xml with RDF feed on display mode, nothing otherwise
     */
    function RSS()
    {
        header('Content-type: application/rss+xml');
        $gid = $this->gadget->request->fetch('id', 'get');

        $rss_path = ROOT_DATA_PATH . 'xml/link-' . $gid . '.rss';
        if (Jaws_FileManagement_File::file_exists($rss_path)) {
            ///FIXME we need to do more error checking over here
            $rss = Jaws_FileManagement_File::file_get_contents($rss_path);
            return $rss;
        }

        $rss = $this->GenerateFeed($gid);
        if (Jaws_Error::IsError($rss)) {
            return '';
        }

        ///FIXME we need to do more error checking over here
        Jaws_FileManagement_File::file_put_contents($rss_path, $rss);
        Jaws_FileManagement_File::chmod($rss_path);

        return $rss;
    }

    /**
     * Generating RDF feed
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   XHTML template content or false on error
     */
    function GenerateFeed($gid)
    {
        $model = $this->gadget->model->load('Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false;
        }
        $links = $model->GetGroupLinks($group['id'], $group['limit_count']);
        if (Jaws_Error::IsError($links)) {
            return false;
        }

        $url    = $this->app->getSiteURL('/');
        $title  = $this->gadget->registry->fetch('site_name', 'Settings');
        $desc   = $this->gadget->registry->fetch('site_description', 'Settings');
        $author = $this->gadget->registry->fetch('site_author', 'Settings');

        $tpl = $this->gadget->template->load('Rdf.html');
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
}