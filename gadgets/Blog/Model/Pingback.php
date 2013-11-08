<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Pingback extends Jaws_Gadget_Model
{
    /**
     * Saves an incoming pingback as a Comment
     *
     * @access  public
     * @param   int     $postID    Post ID
     * @param   string  $sourceURI Who's pinging?
     * @param   string  $permalink Target URI (of post)
     * @param   string  $title     Title of who's pinging (<title>..)
     * @param   string  $content   has the context, from exact target link position (optional)
     */
    function SavePingback($postID, $sourceURI, $permalink, $title, $content)
    {
        $sourceURI = strip_tags($sourceURI);
        $permalink = strip_tags($permalink);

        if (empty($title)) {
            $title   = _t('BLOG_PINGBACK_DEFAULT_TITLE', $sourceURI);
        }

        if (empty($content)) {
            $content = _t('BLOG_PINGBACK_DEFAULT_CONTENT', $sourceURI);
        }

        /**
         * TODO: Find some other default values for pingbacks/trackbacks
         */
        $email = $this->gadget->registry->fetch('gate_email', 'Settings');
        $name  = $this->gadget->registry->fetch('site_author', 'Settings');
        $ip    = $_SERVER['REMOTE_ADDR'];

        $status = $this->gadget->registry->fetch('comment_status');
        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('EditComments');
        $res = $cModel->insertComment(
            $this->gadget->name, $postID, 'Pingback', $name, $email, $sourceURI,
            $content, $ip, $permalink, $status
        );
    }

}