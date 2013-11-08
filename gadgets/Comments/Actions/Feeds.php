<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Comments_Actions_Feeds extends Comments_Actions_Default
{
    /**
     * Displays an Atom feed for blog most recent comments
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function RecentCommentsAtom()
    {
        header('Content-type: application/atom+xml');
        $gadget = jaws()->request->fetch('gadgetname', 'get');
        $commAtom = $this->GetRecentCommentsAtomStruct($gadget, 'atom');
        if (Jaws_Error::IsError($commAtom)) {
            return $commAtom;
        }
        $xml = $commAtom->GetXML();
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays a RSS feed for blog most recent comments
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function RecentCommentsRSS()
    {
        header('Content-type: application/rss+xml');
        $gadget = jaws()->request->fetch('gadgetname', 'get');
        $commAtom = $this->GetRecentCommentsAtomStruct($gadget, 'rss');
        if (Jaws_Error::IsError($commAtom)) {
            return $commAtom;
        }
        $xml = $commAtom->GetXML();
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

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => _t('COMMENTS_GADGETS'),
            'value' => array(
                'Blog' => _t('BLOG_NAME') ,
                'Phoo' => _t('PHOO_NAME') ,
                'Shoutbox' => _t('SHOUTBOX_NAME') ,
                'Comments' => _t('COMMENTS_NAME') ,
            )
        );

        $result[] = array(
            'title' => _t('COMMENTS_FEEDS_TYPE'),
            'value' => array(
                'RSS' => _t('COMMENTS_FEEDS_RSS') ,
                'Atom' => _t('COMMENTS_FEEDS_ATOM') ,
            )
        );
        return $result;
    }

    /**
     * Displays a link to blog feed
     *
     * @access  public
     * @param   string  $gadget gadget name
     * @param   string  $linkType (RSS | Atom)
     * @return  string  XHTML template content
     */
    function FeedsLink($gadget, $linkType)
    {
        $tpl = $this->gadget->template->load('XMLLinks.html');
        if ($linkType == 'RSS') {
            $tpl->SetBlock('recentcomments_rss_link');
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Comments', 'RecentCommentsRSS',
                                                                     array('gadgetname' => $gadget)));
            $tpl->ParseBlock('recentcomments_rss_link');
        } else if ($linkType == 'Atom') {
            $tpl->SetBlock('recentcomments_atom_link');
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Comments', 'RecentCommentsAtom',
                                                                     array('gadgetname' => $gadget)));
            $tpl->ParseBlock('recentcomments_atom_link');
        }
        return $tpl->Get();
    }

    /**
     * Create ATOM struct of recent comments
     *
     * @access  private
     * @param   string  $gadget     Gadget name
     * @param   string  $feed_type  feed type
     * @return  object  Can return the Atom Object
     */
    function GetRecentCommentsAtomStruct($gadget, $feed_type = 'atom')
    {
        $max_title_size = 40;
        $cModel = $this->gadget->model->load('Comments');
        $comments = $cModel->GetComments($gadget);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('COMMENTS_ERROR_GETTING_COMMENTS_ATOMSTRUCT'), _t('COMMENTS_NAME'));
        }

        $commentAtom = new Jaws_AtomFeed();
        $siteURL = $GLOBALS['app']->GetSiteURL('/');
        $url = $GLOBALS['app']->Map->GetURLFor('Comments',
            $feed_type == 'atom'? 'RecentCommentsAtom' : 'RecentCommentsRSS',
            array('gadgetname' => $gadget),
            true);

        $commentAtom->SetTitle($this->gadget->registry->fetch('site_name', 'Settings'));
        $commentAtom->SetLink($url);
        $commentAtom->SetId($siteURL);
        $commentAtom->SetAuthor($this->gadget->registry->fetch('site_author', 'Settings'),
            $GLOBALS['app']->GetSiteURL(),
            $this->gadget->registry->fetch('gate_email', 'Settings'));
        $commentAtom->SetGenerator('JAWS '.$GLOBALS['app']->Registry->fetch('version'));
        $commentAtom->SetCopyright($this->gadget->registry->fetch('copyright', 'Settings'));

        $commentAtom->SetStyle($GLOBALS['app']->GetSiteURL('/gadgets/Comments/Templates/atom.xsl'), 'text/xsl');
        $commentAtom->SetTagLine(_t('COMMENTS_RECENT_COMMENTS', $gadget));

        $objDate = $GLOBALS['app']->loadDate();
        $site = preg_replace('/(.*)\/.*/i', '\\1', $commentAtom->Link->HRef);
        foreach ($comments as $c) {
            $entry_id = $c['reference'];
            $entry = new AtomEntry();
            $entry->SetTitle(($GLOBALS['app']->UTF8->strlen($c['msg_txt']) >= $max_title_size)?
                $GLOBALS['app']->UTF8->substr($c['msg_txt'], 0, $max_title_size).'...' :
                $c['msg_txt']);

            switch ($gadget) {
                case 'Blog':
                    // So we can use the UrlMapping feature.
                    $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView',
                        array('id' => $entry_id),
                        true);

                    $url = $url . htmlentities('#comment' . $c['id']);
                    $entry->SetLink($url);

                    $id = $site . '/blog/' . $entry_id . '/' . $c['id'];
                    $entry->SetId($id);
                    break;

                case 'Phoo':
                    $url = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewImage',
                        array('id' => $entry_id),
                        true);
                    $url = $url . htmlentities('#comment' . $c['id']);
                    $entry->SetLink($url);

                    $id = $site . '/phoo/' . $entry_id . '/' . $c['id'];
                    $entry->SetId($id);
                    break;

                case 'Shoutbox':
                    $url = $GLOBALS['app']->Map->GetURLFor('Shoutbox', 'Comments',
                        array(),
                        true);
                    $url = $url . htmlentities('#comment' . $c['id']);
                    $entry->SetLink($url);

                    $id = $site . '/shoutbox/' . $entry_id . '/' . $c['id'];
                    $entry->SetId($id);
                    break;
            }

            $content = Jaws_String::AutoParagraph($c['msg_txt']);
            $entry->SetSummary($content, 'html');
            $entry->SetContent($content, 'html');
            $entry->SetAuthor($c['name'], $commentAtom->Link->HRef, $c['email']);
            $entry->SetPublished($objDate->ToISO($c['createtime']));
            $entry->SetUpdated($objDate->ToISO($c['createtime']));

            $commentAtom->AddEntry($entry);
            if (!isset($last_modified)) {
                $last_modified = $c['createtime'];
            }
        }
        if (isset($last_modified)) {
            $commentAtom->SetUpdated($objDate->ToISO($last_modified));
        } else {
            $commentAtom->SetUpdated($objDate->ToISO(date('Y-m-d H:i:s')));
        }
        return $commentAtom;
    }

}