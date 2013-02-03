<?php
/**
 * Comments Gadget (layout actions in client side)
 *
 * @category   GadgetLayout
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Comments_LayoutHTML extends Jaws_Gadget_HTML
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
        $limit = array();
        $limit[3] = 3;
        $limit[5] = 5;
        $limit[10] = 10;
        $limit[15] = 15;

        $result[] = array(
            'title' => _t('COMMENTS_LAYOUT_RECENT_COMMENTS'),
            'value' => $limit
        );

        return $result;
    }

    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   mixed   $limit    limit recent comments (int)
     * @return  string  XHTML content
     */
    function RecentComments($limit = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comments = $model->GetRecentComments(null, $limit);

        $tpl = new Jaws_Template('gadgets/Comments/templates/');
        $tpl->Load('RecentComments.html');
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', _t('COMMENTS_RECENT_COMMENTS'));
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            $date = $GLOBALS['app']->loadDate();
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            foreach ($comments as $entry) {
                $tpl->SetBlock('recent_comments/entry');
                $tpl->SetVariable('name', $xss->filter($entry['name']));
                $tpl->SetVariable('email', $xss->filter($entry['email']));
                $tpl->SetVariable('url', $xss->filter($entry['url']));
                $tpl->SetVariable('updatetime', $date->Format($entry['createtime']));
                $tpl->SetVariable('message', $this->gadget->ParseText($entry['msg_txt']));

                $tpl->ParseBlock('recent_comments/entry');
            }
        }
        $tpl->ParseBlock('recent_comments');

        return $tpl->Get();
    }

}