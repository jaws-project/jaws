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
class Comments_Actions_RecentComments extends Comments_Actions_Default
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
                'Blog' => _t('BLOG_NAME') ,
                'Phoo' => _t('PHOO_NAME') ,
                'Shoutbox' => _t('SHOUTBOX_NAME') ,
                'Comments' => _t('COMMENTS_NAME') ,
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

        $tpl = $this->gadget->template->load('RecentComments.html');
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', _t('COMMENTS_RECENT_COMMENTS', _t(strtoupper($gadget) . '_NAME')));
        $tpl->SetVariable('gadget', $gadget);

        $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
        $tpl->SetVariable('comments', $cHTML->ShowComments($gadget, '', 0, array('action' => 'RecentComments'), $limit, $orderBy));

        $tpl->ParseBlock('recent_comments');

        return $tpl->Get();
    }

}