<?php
/**
 * Blog - Tags gadget hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Tags extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a tag content
     *
     * @access  public
     * @param   array  $tag_items  Tag items
     * @return  array  An array of entries that matches a certain pattern
     */
    function Execute($tag_items)
    {
        if(!is_array($tag_items) || empty($tag_items)) {
            return;
        }

        $table = Jaws_ORM::getInstance()->table('blog');
        $table->select('id:integer', 'fast_url', 'title', 'summary', 'text', 'updatetime');
        $result = $table->where('id', $tag_items['post'], 'in')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $posts = array();
        foreach ($result as $r) {
            $post = array();
            $post['title']   = $r['title'];
            $post['url']     = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewPage', array('page' => $r['fast_url']));
            $post['outer']   = true;
            $post['image']   = 'gadgets/Blog/Resources/images/logo.png';
            $post['snippet'] = $r['summary'];
            $post['date']    = $date->ToISO($r['updatetime']);
            $posts[] = $post;
        }

        return $posts;
    }

}