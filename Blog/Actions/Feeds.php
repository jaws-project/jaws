<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Feeds extends Blog_HTML
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
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $id = $xss->defilter($id, true);

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
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $id = $xss->defilter($id, true);

        $xml = $model->MakeCategoryAtom($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for blog most recent comments
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function RecentCommentsAtom()
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $xml = $model->GetRecentCommentsAtom();
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
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $xml = $model->GetRecentCommentsRSS();
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays an Atom feed for most recent comments on the given blog entry
     *
     * @access  public
     * @return  string  xml with Atom feed
     */
    function CommentsAtom()
    {
        header('Content-type: application/atom+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');

        $xml = $model->GetPostCommentsAtom($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

    /**
     * Displays a RSS feed for most recent comments on the given blog entry
     *
     * @access  public
     * @return  string  xml with RSS feed
     */
    function CommentsRSS()
    {
        header('Content-type: application/rss+xml');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');

        $xml = $model->GetPostCommentsRSS($id);
        if (Jaws_Error::IsError($xml)) {
            return '';
        }

        return $xml;
    }

}