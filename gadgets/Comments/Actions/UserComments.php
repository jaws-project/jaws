<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Comments_Actions_UserComments extends Comments_Actions_Default
{
    /**
     * Displays user comments
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UserComments()
    {
        $user = (int) jaws()->request->fetch('user', 'get');
        if(empty($user)) {
            return '';
        }
        $userModel = new Jaws_User();
        $userInfo =  $userModel->GetUser($user);

        $tpl = $this->gadget->template->load('RecentComments.html');
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', _t('COMMENTS_USER_COMMENTS', $userInfo['nickname']));

        $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
        $tpl->SetVariable(
            'comments',
            $cHTML->ShowComments(
                '', // gadget
                '', // action
                0,  // reference
                array('action' => 'RecentComments', 'params' => array('user'=>$user)),
                $user,
                0,  // limit
                0   // order
            )
        );

        $tpl->ParseBlock('recent_comments');

        return $tpl->Get();
    }

}