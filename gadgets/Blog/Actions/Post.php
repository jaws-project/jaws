<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Post extends Blog_Actions_Default
{
    /**
     * Displays a list of recent blog entries ordered by date
     *
     * @access  public
     * @return  mixed   XHTML template content on Success or False on error
     */
    function LastPost()
    {
        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('Atom'),
                'type'  => 'application/atom+xml',
                'rel'   => 'alternate',
                'title' => 'Atom - All'
            )
        );
        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('RSS'),
                'type'  => 'application/rss+xml',
                'rel'   => 'alternate',
                'title' => 'RSS 2.0 - All'
            )
        );

        $model = $this->gadget->model->load('Posts');
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
     * @return  string  XHTML template content
     */
    function SingleView($id = null)
    {
        $g_id = $this->gadget->request->fetch('id', 'get');
        $g_id = Jaws_XSS::defilter($g_id);

        $model = $this->gadget->model->load('Posts');
        if (is_null($id)) {
            $entry = $model->GetEntry($g_id, true);
        } else {
            $entry = $model->GetEntry($id, true);
        }

        if (!Jaws_Error::IsError($entry) && !empty($entry)) {
            foreach ($entry['categories'] as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat['id'])) {
                    return Jaws_HTTPError::Get(403);
                }
            }

            //increase entry's visits counter
            $model->ViewEntry($entry['id']);
            $entry['clicks']++;

            if ($this->gadget->registry->fetch('pingback') == 'true') {
                $pback = Jaws_Pingback::getInstance();
                $pback->showHeaders($this->gadget->urlMap('Pingback', array(), true));
            }

            $this->SetTitle($entry['title']);
            $this->AddToMetaKeywords($entry['meta_keywords']);
            $this->SetDescription($entry['meta_description']);
            $tpl = $this->gadget->template->load('Post.html');
            $tpl->SetBlock('single_view');
            $this->ShowEntry($tpl, 'single_view', $entry, false);

            $trbkHTML = $this->gadget->action->load('Trackbacks');
            if (!Jaws_Error::IsError($trbkHTML)) {
                $tpl->SetVariable('trackbacks', $trbkHTML->ShowTrackbacks($entry['id']));
            }

            $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
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
                                  $this->gadget->registry->fetch('allow_comments') == 'true' &&
                                  $allow_comments_config;

                $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
                $tpl->SetVariable(
                    'comments',
                    $cHTML->ShowComments(
                        'Blog', 'Post', $entry['id'],
                        array(
                            'action' => 'SingleView',
                            'params' => array(
                                'id' => empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url']
                            )
                        )
                    )
                );

                if ($allow_comments) {
                    $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm(
                        'Blog',
                        'Post',
                        $entry['id']
                    ));
                } elseif ($restricted) {
                    $login_url = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                    $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                    $tpl->SetVariable('comment-form', _t('COMMENTS_COMMENTS_RESTRICTED', $login_url, $register_url));
                }
            }

            if ($tpl->VariableExists('navigation')) {
                $navtpl = $this->gadget->template->load('PostNavigation.html');
                if ($prev = $model->GetNOPEntry($entry['id'], 'previous')) {
                    $navtpl->SetBlock('entry-navigation/previous');
                    $navtpl->SetVariable(
                        'url',
                        $this->gadget->urlMap(
                            'SingleView',
                            array('id' => empty($prev['fast_url'])? $prev['id'] : $prev['fast_url'])
                        )
                    );
                    $navtpl->SetVariable('title', $prev['title']);
                    $navtpl->SetVariable('previous', _t('GLOBAL_PREVIOUS'));
                    $navtpl->ParseBlock('entry-navigation/previous');
                }

                if ($next = $model->GetNOPEntry($entry['id'], 'next')) {
                    $navtpl->SetBlock('entry-navigation/next');
                    $navtpl->SetVariable(
                        'url',
                        $this->gadget->urlMap(
                            'SingleView',
                            array('id' => empty($next['fast_url'])? $next['id'] : $next['fast_url'])
                        )
                    );
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
            return Jaws_HTTPError::Get(404);
        }
    }

}