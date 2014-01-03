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
class Blog_Model_Trackbacks extends Jaws_Gadget_Model
{
    /**
     * Get trackbacks
     *
     * @access  public
     * @param   int     $id     ID of the Entry
     * @return  mixed   A list of the trackbacks, if blog is not using trackback returns true,
     *                  but if blog is using trackback but was not fetched correctly will returns false or Jaws_Error on error
     */
    function GetTrackbacks($id)
    {
        if ($this->gadget->registry->fetch('trackback') == 'true') {
            $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
            $trackbackTable->select(
                'id:integer', 'parent_id:integer', 'url', 'title', 'excerpt', 'blog_name', 'createtime'
            )->where('parent_id', $id)->and()->where('status', 'approved')->orderBy('createtime asc');
            $result = $trackbackTable->fetchAll();

            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('BLOG_ERROR_GETTING_TRACKBACKS'));
            }

            $entries = array();
            foreach ($result as $r) {
                $r['createtime'] = $r['createtime'];
                $entries[] = $r;
            }

            return $entries;
        }

        return true;
    }

    /**
     * Get trackbacks
     *
     * @access  public
     * @param   int     $id     ID of the Trackback
     * @return  mixed   Properties of a trackback and Jaws_Error on error
     */
    function GetTrackback($id)
    {
        $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
        $result = $trackbackTable->select(
            'id:integer', 'parent_id:integer', 'url', 'title', 'excerpt', 'blog_name', 'ip', 'createtime', 'updatetime'
        )->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_TRACKBACKS'));
        }

        $entries = array(
            'id'         => isset($result['id']) ? $result['id'] : null,
            'parent_id'  => isset($result['parent_id']) ? $result['parent_id'] : null,
            'url'        => isset($result['url']) ? $result['url'] : null,
            'title'      => isset($result['title']) ? $result['title'] : null,
            'excerpt'    => isset($result['excerpt']) ? $result['excerpt'] : null,
            'blog_name'  => isset($result['blog_name']) ? $result['blog_name'] : null,
            'ip'         => isset($result['ip']) ? $result['ip'] : null,
            'createtime' => isset($result['createtime']) ? $result['createtime'] : null,
            'updatetime' => isset($result['updatetime']) ? $result['updatetime'] : null
        );

        return $entries;
    }

    /**
     * Create a new trackback
     *
     * @access  public
     * @param   int     $parent_id      ID of the entry
     * @param   string  $url            URL of the trackback
     * @param   string  $title          Title of the trackback
     * @param   string  $excerpt        The Excerpt
     * @param   string  $blog_name      The name of the Blog
     * @param   string  $ip             The sender ip address
     * @return  mixed   True if trackback was successfully added, if not, returns Jaws_Error
     */
    function NewTrackback($parent_id, $url, $title, $excerpt, $blog_name, $ip)
    {
        if ($this->gadget->registry->fetch('trackback') == 'true') {
            $model = $this->gadget->model->load('Posts');
            if (!$model->DoesEntryExists($parent_id)) {
                return new Jaws_Error(_t('BLOG_ERROR_DOES_NOT_EXISTS'));
            }

            // lets only load it if it's actually needed
            $now = $GLOBALS['db']->Date();

            $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
            $trackbackTable->select('id:integer')->where('parent_id', $parent_id);
            $id = $trackbackTable->and()->where('url', strip_tags($url))->fetchOne();

            $trackData['title']         = strip_tags($title);
            $trackData['excerpt']       = strip_tags($excerpt);
            $trackData['blog_name']     = strip_tags($blog_name);
            $trackData['updatetime']    = $now;

            $trackbackTable = Jaws_ORM::getInstance()->table('blog_trackback');
            if (!Jaws_Error::IsError($id) && !empty($id)) {
                $trackbackTable->update($trackData)->where('id', $id);
            } else {
                $trackData['parent_id']     = $parent_id;
                $trackData['url']           = strip_tags($url);
                $trackData['ip']            = $ip;
                $trackData['status']        = $this->gadget->registry->fetch('trackback_status');
                $trackData['createtime']    = $now;
                $trackbackTable->insert($trackData);
            }

            $result = $trackbackTable->exec();
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('BLOG_ERROR_TRACKBACK_NOT_ADDED'));
            }

            return true;
        }

        return true;
    }
}