<?php
/**
 * Blog - Rating gadget hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Rating extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a rating content
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   array   $references Array of References
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($action, $references)
    {
        if(empty($action) || !is_array($references) ||empty($references)) {
            return false;
        }

        $table = Jaws_ORM::getInstance()->table('blog');
        $table->select('id:integer', 'fast_url', 'title', 'summary', 'text', 'updatetime');
        $result = $table->where('id', $references, 'in')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $posts = array();
        foreach ($result as $r) {
            $post = array();
            $post['title']   = $r['title'];
            $post['url']     = $this->gadget->urlMap('ViewPage', array('page' => $r['fast_url']));
            $post['outer']   = false;
            $post['image']   = 'gadgets/Blog/Resources/images/logo.png';
            $post['snippet'] = $r['summary'];
            $post['date']    = $date->ToISO($r['updatetime']);
            $posts[$r['id']] = $post;
        }

        return $posts;
    }

}