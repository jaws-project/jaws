<?php
/**
 * SimpleSite AJAX API
 *
 * @category   Ajax
 * @package    SimpleSite
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SimpleSiteAdminAjax extends Jaws_Ajax
{
    /**
     * Get simplesites with items
     *
     * @access  public
     * @return  array   Data
     */
    function GetItems()
    {
        $data = $this->_Model->GetItems();
        if (Jaws_Error::IsError($data)) {
            return false;
        }
        return $data;
    }

    /**
     * Get references
     * @param   string  $type   Type of references
     * @access  public
     * @return  array Data
     */
    function GetReferences($type)
    {
        switch($type) {
            case 'StaticPage':
                return $this->GetStaticPageReferences();
                break;
            case 'Blog':
                return $this->GetBlogReferences();
                break;
            case 'Launcher':
                return $this->GetLauncherReferences();
                break;
            default:
                return false;
        }
    }

    /**
     * Get the references for StaticPages...
     *
     * @access  public
     * @return  array Data
     */
    function GetStaticPageReferences()
    {
        $staticPage = $GLOBALS['app']->loadGadget('StaticPage', 'Model');
        $pages = $staticPage->GetPages();
        if (Jaws_Error::IsError($pages)) {
            return array();
        }
        $result = array();
        foreach ($pages as $page) {
            $result[$page['base_id']] = '[' . $page['base_id'] . '] ' . $page['title'];
        }
        return $result;
    }

    /**
     * Get the references for Blog...
     *
     * @access  public
     * @return  array Data
     */
    function GetBlogReferences()
    {
        $blog = $GLOBALS['app']->loadGadget('Blog', 'Model');
        $posts = $blog->GetEntriesAsArchive();
        if (Jaws_Error::IsError($posts)) {
            return array();
        }
        $result = array();
        foreach ($posts as $post) {
            $result[$post['id']] = '[' . $post['createtime'] . '] ' . $post['title'];
        }
        return $result;
    }

    /**
     * Get the references for Launcher...
     *
     * @access  public
     * @return  array Data
     */
    function GetLauncherReferences()
    {
        $launcher = $GLOBALS['app']->loadGadget('Launcher', 'Model');
        $items = $launcher->GetLaunchers();
        $result = array();
        if (Jaws_Error::IsError($items)) {
            return array();
        }
        foreach ($items as $k => $v) {
            $result[$v] = $v;
        }
        return $result;
    }

    /**
     * Create a new item
     *
     * @access  public
     * @param   int      $parent_id  Parent Id
     * @param   string   $title      Item title
     * @param   string   $shortname  Item shortname (also used in paths)
     * @param   string   $type       Item type (URL, StaticPage, Blog, etc)
     * @param   string   $reference  Type reference (e.g. ID of the static page)
     * @param   string   $change     Change frequency. Values can be always, hourly, daily, weekly,
     *                              monthly, yearly, never
     * @param   string   $priority   Priority of this item relative to other item on the site. Can be 
     *                              values from 1 to 5 (only numbers!).
     * @return  array New item data
     */
    function NewItem($parent_id, $title, $shortname, $type, $reference, $change, $priority)
    {
        if ($change == 'none') {
            $change = null;
        }

        $result   = $this->_Model->NewItem($parent_id, $title, $shortname, $type, 
                                           $reference, $change, $priority);
        if (Jaws_Error::IsError($result)) {
            $response = $GLOBALS['app']->Session->PopLastResponse();
        } else {
            $response = array_merge($GLOBALS['app']->Session->PopLastResponse(), $result);
        }

        return $response;
    }

    /**
     * Update item
     *
     * @access  public
     * @param   int      $id         Item Id
     * @param   int      $parent_id  Parent Id
     * @param   string   $title      Item title
     * @param   string   $shortname  Item shortname (also used in paths)
     * @param   string   $type       Item type (URL, StaticPage, Blog, etc)
     * @param   string   $reference  Type reference (e.g. ID of the static page)
     * @param   string   $change     Change frequency. Values can be always, hourly, daily, weekly,
     *                              monthly, yearly, never
     * @param   string   $priority   Priority of this item relative to other item on the site. Can be 
     *                              values from 1 to 5 (only numbers!).
     * @return  array New item data
     */
    function UpdateItem($id, $parent_id, $title, $shortname, $type, $reference, $change, $priority)
    {
        if ($change == 'none') {
            $change = null;
        }

        $this->_Model->UpdateItem($id, $parent_id, $title, $shortname, $type, 
                                  $reference, $change, $priority);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a given item
     *
     * @param   int      $id Item Id.
     * @access  public
     * @return  bool    True if succesful
     */
    function DeleteItem($id)
    {
        $this->_Model->DeleteItem($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Move a given item to a given direction
     *
     * @param   int      $id         Item Id
     * @param   string   $direction  up or down
     * @access  public
     * @return  bool    True if succesful
     */
    function MoveItem($id, $direction)
    {
        $this->_Model->MoveItem($id, $direction);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Send the sitemap XML URL to search engines
     *
     * @access  public
     * @return  bool    True if succesful
     */
    function PingSitemap()
    {
        $this->CheckSession('SimpleSite', 'PingSite');
        $this->_Model->ping(true);
        $GLOBALS['app']->Session->PushLastResponse(_t('SIMPLESITE_SITEMAP_SENT'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}