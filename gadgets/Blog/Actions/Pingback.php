<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Pingback extends Blog_Actions_Default
{
    /**
     * Pingback function
     *
     * @access  public
     */
    function Pingback()
    {
        if ($this->gadget->registry->fetch('pingback') == 'true') {
            $pback =& Jaws_PingBack::getInstance();
            $response = $pback->listen();
            if (is_array($response)) {
                //Load model
                $model = $this->gadget->loadModel('Posts');

                //We need to parse the target URI to get the post ID
                $GLOBALS['app']->Map->Parse($response['targetURI']);

                //pingbacks come from POST but JawsURL maps everything on get (that how Maps work)
                $postID = jaws()->request->fetch('id', 'get');
                if (empty($postID)) {
                    return;
                }

                $entry  = $model->GetEntry($postID, true);
                if (!Jaws_Error::IsError($entry)) {
                    $title   = '';
                    $content = '';

                    $response['title'] = strip_tags($response['title']);

                    if (empty($response['title'])) {
                        if (empty($entry['title'])) {
                            $title = _t('GLOBAL_RE')._t('BLOG_PINGBACK_TITLE', $entry['title']);
                            $content = _t('BLOG_PINGBACK_DEFAULT_COMMENT', $entry['sourceURI']);
                        }
                    } else {
                        $comesFrom = '<a href="'.$response['sourceURI'].'">'.$response['title'].'</a>';
                        $content = _t('BLOG_PINGBACK_COMMENT', $comesFrom);
                        $title = _t('GLOBAL_RE')._t('BLOG_PINGBACK_TITLE', $response['title']);
                    }
                    $model->SavePingback($postID, $response['sourceURI'], $response['targetURI'], $title, $content);
                }
            }
        }
    }

}