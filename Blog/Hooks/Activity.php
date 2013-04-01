<?php
/**
 * Blog - UserActivity gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlogActivityHook
{
    /**
     * Returns user's activity array
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  array   An array of user activity
     */
    function Hook($uid, $uname)
    {
        $entity = array();
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
        $entity['url'] = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewAuthorPage', array('id' => $uname));

        return array($entity);
    }

}