<?php
/**
 * Blog - UserActivity gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlogUserActivityHook
{
    /**
     * Returns an array with the results of a user activity
     *
     * @access  public
     * @param   string  $uid   User id
     * @return  array   An array of user activity
     */
    function Hook($uid)
    {
        $entity = array();

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();
        $user = $userModel->GetUser($uid);

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'count(id) as post_count:integer'
        );

        $postCount = $blogTable->where('user_id', $uid)->and()->where('published', true)->getOne();
        if ($postCount == 0) {
            return array();
        }

        $entity['title'] = _t('BLOG_ENTRY');
        $entity['count'] = $postCount;
        $entity['url'] = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewAuthorPage', array('id' => $user['username']));


        return array($entity);
    }
}
