<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright  2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_MostCommented extends Jaws_Gadget_Action
{
    /**
     * Get MostCommented action params
     *
     * @access  public
     * @return  array list of MostCommented action params
     */
    function MostCommentedLayoutParams()
    {
        $result = array();

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => _t('COMMENTS_GADGETS'),
            'value' => array(
                '' => _t('GLOBAL_ALL') ,
                'Blog' => _t('BLOG_TITLE') ,
                'Phoo' => _t('PHOO_TITLE') ,
                'Shoutbox' => _t('SHOUTBOX_TITLE') ,
                'Comments' => _t('COMMENTS_TITLE') ,
            )
        );


        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' => $this->gadget->registry->fetch('recent_comment_limit')
        );

        return $result;
    }


    /**
     * Displays recent comments
     *
     * @access  public
     * @param   string  $gadget
     * @param   mixed   $limit    limit recent comments (int)
     * @return  string  XHTML content
     */
    function MostCommented($gadget = '', $limit = 0)
    {
        $entries = Jaws_Gadget::getInstance('Comments')
            ->model->load('Comments')
            ->MostCommented($gadget, $limit);
        if (Jaws_Error::IsError($entries) || empty($entries)) {
            return false;
        }

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);

        $gadget_name = (empty($gadget)) ? _t('GLOBAL_ALL') : _t(strtoupper($gadget) . '_TITLE');
        $tpl = $this->gadget->template->load('MostCommented.html');
        $tpl->SetBlock('comments');
        $tpl->SetVariable('title', _t('COMMENTS_MOST_COMMENTED', $gadget_name));
        if(!empty($gadget)) {
            $tpl->SetVariable('gadget', $gadget);
        }

        foreach ($entries as $entry) {
            $tpl->SetBlock('comments/entry');
            $tpl->SetVariable('link', $entry['reference_link']);
            $tpl->SetVariable('title', $entry['reference_title']);
            $tpl->ParseBlock('comments/entry');
        }

        $tpl->ParseBlock('comments');
        return $tpl->Get();
    }

}