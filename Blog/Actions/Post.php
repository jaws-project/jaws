<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Post extends Blog_HTML
{
    /**
     * Displays a list of recent blog entries ordered by date
     *
     * @access  public
     * @return  mixed   XHTML template content on Success or False on error
     */
    function LastPost()
    {
        $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog', 'Atom'),
                                             'alternate',
                                             'application/atom+xml',
                                             'Atom - All');
        $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog', 'RSS'),
                                             'alternate',
                                             'application/rss+xml',
                                             'RSS 2.0 - All');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $id = $model->GetLatestPublishedEntryID();
        if (!Jaws_Error::IsError($id) && !empty($id)) {
            return $this->SingleView($id);
        }

        return false;
    }

    /**
     * Displays a given blog entry
     *
     * @access  public
     * @param   int     $id                 Post id (optional, null by default)
     * @param   bool    $preview_mode       Display comments flag (optional, false by default)
     * @param   string  $reply_to_comment   reply string
     * @return  string  XHTML template content
     */
    function SingleView($id = null, $preview_mode = false, $reply_to_comment = '')
    {
        $request =& Jaws_Request::getInstance();
        $g_id = $request->get('id', 'get');
        $g_id = Jaws_XSS::defilter($g_id, true);

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        if (is_null($id)) {
            $entry = $model->GetEntry($g_id, true);
        } else {
            $entry = $model->GetEntry($id, true);
        }

        if (!Jaws_Error::IsError($entry) && !empty($entry)) {
            //increase entry's visits counter
            $res = $model->ViewEntry($entry['id']);
            $entry['clicks']++;

            if ($this->gadget->registry->get('pingback') == 'true') {
                require_once JAWS_PATH . 'include/Jaws/Pingback.php';
                $pback =& Jaws_PingBack::getInstance();
                $pback->showHeaders($this->gadget->GetURLFor('Pingback', array(), true));
            }

            $this->SetTitle($entry['title']);
            $this->AddToMetaKeywords($entry['meta_keywords']);
            $this->SetDescription($entry['meta_description']);
            $tpl = new Jaws_Template('gadgets/Blog/templates/');
            $tpl->Load('Post.html', true);
            $tpl->SetBlock('single_view');
            $res = $this->ShowEntry($tpl, 'single_view', $entry, false);

            $trbkHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Trackbacks');
            if (!Jaws_Error::IsError($trbkHTML)) {
                $tpl->SetVariable('trackbacks', $trbkHTML->ShowTrackbacks($entry['id']));
            }

            $allow_comments_config = $this->gadget->registry->get('allow_comments', 'Comments');
            switch ($allow_comments_config) {
                case 'restricted':
                    $allow_comments_config = $GLOBALS['app']->Session->Logged();
                    $restricted = !$allow_comments_config;
                    break;

                default:
                    $restricted = false;
                    $allow_comments_config = $allow_comments_config == 'true';
            }

            if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
                $allow_comments = $entry['allow_comments'] === true &&
                                  $this->gadget->registry->get('allow_comments') == 'true' &&
                                  $allow_comments_config;
                $commentsHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Comments');
                if (empty($reply_to_comment)) {
                    $tpl->SetVariable(
                        'comments',
                        $commentsHTML->ShowComments(
                            $entry['id'],
                            $entry['fast_url'],
                            0,
                            0,
                            1,
                            (int)$allow_comments
                        )
                    );
                    if ($allow_comments) {
                        if ($preview_mode) {
                            $tpl->SetVariable('preview', $commentsHTML->ShowPreview());
                        }
                        $tpl-> SetVariable(
                            'comment-form',
                            $commentsHTML->DisplayCommentForm(
                                $entry['id'],
                                0,
                                _t('GLOBAL_RE').$entry['title']
                            )
                        );
                    } elseif ($restricted) {
                        $login_url    = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                        $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                        $tpl->SetVariable('comment-form', _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url));
                    }
                } else {
                    $tpl->SetVariable('comments', $commentsHTML->ShowSingleComment($reply_to_comment));
                    if ($allow_comments) {
                        if ($preview_mode) {
                            $tpl->SetVariable('preview', $commentsHTML->ShowPreview());
                        }
                        $title  = $entry['title'];
                        $tpl->SetVariable(
                            'comment-form',
                            $commentsHTML->DisplayCommentForm(
                                $entry['id'],
                                $reply_to_comment,
                                _t('GLOBAL_RE'). $title
                            )
                        );
                    } elseif ($restricted) {
                        $login_url    = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                        $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                        $tpl->SetVariable(
                            'comment-form',
                            _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url)
                        );
                    }
                }
            }

            if ($tpl->VariableExists('navigation')) {
                $navtpl = new Jaws_Template('gadgets/Blog/templates/');
                $navtpl->Load('PostNavigation.html');
                if ($prev = $model->GetNOPEntry($entry['id'], 'previous')) {
                    $navtpl->SetBlock('entry-navigation/previous');
                    $navtpl->SetVariable('url', $this->gadget->GetURLFor('SingleView',
                                                                       array('id' => empty($prev['fast_url']) ?
                                                                             $prev['id'] : $prev['fast_url'])));
                    $navtpl->SetVariable('title', $prev['title']);
                    $navtpl->SetVariable('previous', _t('GLOBAL_PREVIOUS'));
                    $navtpl->ParseBlock('entry-navigation/previous');
                }

                if ($next = $model->GetNOPEntry($entry['id'], 'next')) {
                    $navtpl->SetBlock('entry-navigation/next');
                    $navtpl->SetVariable('url', $this->gadget->GetURLFor('SingleView',
                                                                   array('id' => empty($next['fast_url']) ?
                                                                         $next['id'] : $next['fast_url'])));
                    $navtpl->SetVariable('title', $next['title']);
                    $navtpl->SetVariable('next', _t('GLOBAL_NEXT'));
                    $navtpl->ParseBlock('entry-navigation/next');
                }
                $navtpl->ParseBlock('entry-navigation');
                $tpl->SetVariable('navigation', $navtpl->Get());
            }

            $tpl->ParseBlock('single_view');
            return $tpl->Get();
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
    }

}