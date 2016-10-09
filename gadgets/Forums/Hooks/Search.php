<?php
/**
 * Forums - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   array of search fields
     */
    function GetOptions() {
        return array(
            'forums_posts' => array('fp.message'),
        );
    }


    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $postNumber = Jaws_ORM::getInstance()->table('forums_posts', 'fpc');
        $postNumber->select('count(fpc.id):integer')->where('fpc.tid', array('fp.tid', 'expr'))
            ->and()->where('fpc.id', array('fp.id', 'expr'), '<=');
        $postNumber->alias('post_number');

        $objORM->table('forums_posts', 'fp');
        $objORM->select('fp.id', 'fp.tid', 'ft.fid', 'ft.subject', 'fp.message', 'fp.insert_time', $postNumber);
        $objORM->join('forums_topics as ft', 'fp.tid', 'ft.id', 'left');
        $result = $objORM->loadWhere('search.terms')->orderBy('fp.insert_time desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $objDate = Jaws_Date::getInstance();
        $posts = array();
        foreach ($result as $r) {
            if (!$this->gadget->GetPermission('ForumPublic', $r['fid'])) {
                continue;
            }

            $post = array();
            $post['title'] = $r['subject'];
            $post['url']   = $this->gadget->urlMap(
                'Posts',
                array(
                    'fid' => $r['fid'],
                    'tid' => $r['tid'],
                    'page' => ceil($r['post_number']/10)
                )
            );
            $post['image']   = 'gadgets/Forums/Resources/images/logo.png';
            $post['snippet'] = $r['message'];
            $post['date']    = $objDate->ToISO($r['insert_time']);
            $posts[] = $post;
        }

        return $posts;
    }

}