<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_AuthorPosts extends Jaws_Gadget_Model
{
    /**
     * Get number of author's pages
     *
     * @access  public
     * @param   string  $user   username
     * @return  int number of pages
     */
    function GetAuthorNumberOfPages($user)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('count(blog.id)');
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');
        $blogTable->where('published', true)->and()->where('publishtime', $GLOBALS['db']->Date(), '<=');
        if (is_numeric($user)) {
            $blogTable->and()->where('users.id', $user);
        } else {
            $blogTable->and()->where('users.username', $user);
        }
        $howmany = $blogTable->fetchOne();
        return Jaws_Error::IsError($howmany)? 0 : $howmany;
    }
}