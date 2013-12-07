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
class Blog_Model_Admin_Settings extends Jaws_Gadget_Model
{

    /**
     * Get all the main settings of the Blog
     *
     * @access  public
     * @return  array   An array of settings
     */
    function GetSettings()
    {
        $settings = array();
        $settings['default_view']               = $this->gadget->registry->fetch('default_view');
        $settings['last_entries_limit']         = $this->gadget->registry->fetch('last_entries_limit');
        $settings['popular_limit']              = $this->gadget->registry->fetch('popular_limit');
        $settings['default_category']           = $this->gadget->registry->fetch('default_category');
        $settings['xml_limit']                  = $this->gadget->registry->fetch('xml_limit');
        $settings['comments']                   = $this->gadget->registry->fetch('allow_comments');
        $settings['trackback']                  = $this->gadget->registry->fetch('trackback');
        $settings['trackback_status']           = $this->gadget->registry->fetch('trackback_status');
        $settings['last_comments_limit']        = $this->gadget->registry->fetch('last_comments_limit');
        $settings['last_recentcomments_limit']  = $this->gadget->registry->fetch('last_recentcomments_limit');
        $settings['comment_status']             = $this->gadget->registry->fetch('comment_status');
        $settings['pingback']                   = $this->gadget->registry->fetch('pingback');

        return $settings;
    }

    /**
     * Save the main settings of the Blog
     *
     * @access  public
     * @param   string  $view                   The default View
     * @param   int     $limit                  Limit of entries that blog will show
     * @param   int     $popularLimit           Limit of popular entries
     * @param   int     $commentsLimit          Limit of comments that blog will show
     * @param   int     $recentcommentsLimit    Limit of recent comments to display
     * @param   string  $category               The default category for blog entries
     * @param   int     $xml_limit              xml limit
     * @param   bool    $comments               If comments should appear
     * @param   string  $comment_status         Default comment status
     * @param   bool    $trackback              If Trackback should be used
     * @param   string  $trackback_status       Default trackback status
     * @param   bool    $pingback               If Pingback should be used
     * @return  mixed   Return True if settings were saved without problems, else Jaws_Error
     */
    function SaveSettings($view, $limit, $popularLimit, $commentsLimit, $recentcommentsLimit, $category,
                          $xml_limit, $comments, $comment_status, $trackback, $trackback_status,
                          $pingback)
    {
        $result = array();
        $result[] = $this->gadget->registry->update('default_view', $view);
        $result[] = $this->gadget->registry->update('last_entries_limit', $limit);
        $result[] = $this->gadget->registry->update('popular_limit', $popularLimit);
        $result[] = $this->gadget->registry->update('default_category', $category);
        $result[] = $this->gadget->registry->update('xml_limit', $xml_limit);
        $result[] = $this->gadget->registry->update('allow_comments', $comments);
        $result[] = $this->gadget->registry->update('comment_status', $comment_status);
        $result[] = $this->gadget->registry->update('trackback', $trackback);
        $result[] = $this->gadget->registry->update('trackback_status', $trackback_status);
        $result[] = $this->gadget->registry->update('last_comments_limit', $commentsLimit);
        $result[] = $this->gadget->registry->update('last_recentcomments_limit', $recentcommentsLimit);
        $result[] = $this->gadget->registry->update('pingback', $pingback);

        foreach ($result as $r) {
            if (!$r || Jaws_Error::IsError($r)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('BLOG_ERROR_SETTINGS_NOT_SAVE'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BLOG_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }

}