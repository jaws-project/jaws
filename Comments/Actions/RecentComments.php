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
class Comments_Actions_RecentComments extends Comments_HTML
{
    /**
     * Get RecentComments action params
     *
     * @access  public
     * @return  array list of RecentComments action params
     */
    function RecentCommentsLayoutParams()
    {
        $result = array();

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => _t('COMMENTS_GADGETS'),
            'value' => array(
                'blog' => _t('BLOG_NAME') ,
                'phoo' => _t('PHOO_NAME') ,
                'shoutbox' => _t('SHOUTBOX_NAME') ,
                'comments' => _t('COMMENTS_NAME') ,
            )
        );


        $result[] = array(
            'title' => _t('GLOBAL_ORDERBY'),
            'value' => array(
                1 => _t('GLOBAL_CREATETIME'). ' &uarr;',
                0 => _t('GLOBAL_CREATETIME'). ' &darr;',
            )
        );

        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' => $this->gadget->registry->fetch('recent_comment_limit')
        );

        return $result;
    }


    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   string  $gadget
     * @param   int     $orderBy
     * @param   mixed   $limit    limit recent comments (int)
     * @return  string  XHTML content
     */
    function RecentComments($gadget,  $orderBy = 0, $limit = 0)
    {
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $GLOBALS['app']->Translate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $GLOBALS['app']->Translate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();

        $model = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comments = $model->GetComments('comments', $limit, null, null, array(COMMENT_STATUS_APPROVED), false,
            null, $orderBy);

        $tpl = new Jaws_Template('gadgets/Comments/templates/');
        $tpl->Load('RecentComments.html');
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', _t('COMMENTS_RECENT_COMMENTS', _t(strtoupper($gadget) . '_NAME')));
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($comments as $entry) {
                $tpl->SetBlock('recent_comments/entry');
                $tpl->SetVariable('name', Jaws_XSS::filter($entry['name']));
                $tpl->SetVariable('email', Jaws_XSS::filter($entry['email']));
                $tpl->SetVariable('url', Jaws_XSS::filter($entry['url']));
                $tpl->SetVariable('updatetime', $date->Format($entry['createtime']));
                $tpl->SetVariable('message', Jaws_String::AutoParagraph($entry['msg_txt']));

                $tpl->ParseBlock('recent_comments/entry');

                if (!empty($entry['reply'])) {
                    $user = $userModel->GetUser((int)$entry['replier'], true, true);
                    $tpl->SetBlock('recent_comments/reply');
                    $tpl->SetVariable('reply', $entry['reply']);
                    $tpl->SetVariable('replier', $user['nickname']);
                    $tpl->SetVariable('url', $user['url']);
                    $tpl->SetVariable('email', $user['email']);
                    $tpl->SetVariable('lbl_reply', _t('COMMENTS_REPLY'));
                    $tpl->ParseBlock('recent_comments/reply');
                }

            }
        }
        $tpl->ParseBlock('recent_comments');

        return $tpl->Get();
    }

}