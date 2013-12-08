<?php
/**
 * Comments gadget hook
 *
 * @category    GadgetHook
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 */
class Comments_Hooks_Activity extends Jaws_Gadget_Hook
{
    /**
     * Returns Comments array
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  array   An array of user activity
     */
    function Execute($uid, $uname)
    {
        $entity = array();
        $model = $this->gadget->model->load('Comments');
        $commentsCount = $model->GetCommentsCount('', '', '', '', array(), $uid);

        if ($commentsCount > 0) {
            $entity[0]['title'] = _t('COMMENTS_COMMENTS');
            $entity[0]['count'] = $commentsCount;
            $entity[0]['url'] = $this->gadget->urlMap('UserComments', array('uid' => $uid));
        }

        return $entity;
    }

}