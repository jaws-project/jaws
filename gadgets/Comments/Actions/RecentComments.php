<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2012-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_RecentComments extends Jaws_Gadget_Action
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
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => $this::t('GADGETS'),
            'value' => array(
                '' => Jaws::t('ALL') ,
                'Blog' => $this::t('BLOG.TITLE') ,
                'Phoo' => $this::t('PHOO.TITLE') ,
                'Shoutbox' => $this::t('SHOUTBOX.TITLE') ,
                'Comments' => $this::t('TITLE') ,
            )
        );


        $result[] = array(
            'title' => Jaws::t('ORDERBY'),
            'value' => array(
                1 => Jaws::t('CREATETIME'). ' &uarr;',
                2 => Jaws::t('CREATETIME'). ' &darr;',
            )
        );

        $result[] = array(
            'title' => Jaws::t('COUNT'),
            'value' => $this->gadget->registry->fetch('recent_comment_limit')
        );

        return $result;
    }


    /**
     * Displays recent comments
     *
     * @access  public
     * @param   string  $gadget
     * @param   int     $orderBy
     * @param   mixed   $limit    limit recent comments (int)
     * @return  string  XHTML content
     */
    function RecentComments($gadget = '', $orderBy = 0, $limit = 0)
    {
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);

        $gadget_name = (empty($gadget)) ? Jaws::t('ALL') : $this::t($gadget. '.TITLE');

        if ($this->app->requestedActionMode === 'normal') {
            $tFilename = 'RecentComments.html';
        } else {
            $tFilename = 'RecentComments0.html';
        }
        $tpl = $this->gadget->template->load($tFilename);
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', $this::t('RECENT_COMMENTS', $gadget_name));
        if(!empty($gadget)) {
            $tpl->SetVariable('gadget', $gadget);
        }

        $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
        $tpl->SetVariable(
            'comments', $cHTML->ShowComments(
                $gadget,
                '',
                0,
                array('action' => 'RecentComments'),
                null,
                $limit,
                $orderBy
            )
        );

        $tpl->ParseBlock('recent_comments');
        return $tpl->Get();
    }

}