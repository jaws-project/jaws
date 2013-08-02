<?php
/**
 * Sitemap AJAX API
 *
 * @category   Ajax
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Sitemap_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }

    /**
     * Gets sitemaps with items
     *
     * @access  public
     * @return  array   List of items
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
     * Gets references
     *
     * @access  public
     * @param   string  $type   Type of references
     * @return  mixed   Array of references or false
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
     * Gets references for StaticPages...
     *
     * @access  public
     * @return  array   Array of references
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
     * Gets references for Blog...
     *
     * @access  public
     * @return  array   Array of references
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
            $result[$post['id']] = '[' . $post['publishtime'] . '] ' . $post['title'];
        }
        return $result;
    }

    /**
     * Gets references for Launcher...
     *
     * @access  public
     * @return  array   Array of references
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
     * Creates a new item
     *
     * @access  public
     * @param   int     $parent_id  Parent ID
     * @param   string  $title      Item title
     * @param   string  $shortname  Item shortname (also used in paths)
     * @param   string  $type       Item type (URL, StaticPage, Blog, etc)
     * @param   string  $reference  Type reference (e.g. ID of the static page)
     * @param   string  $change     Change frequency. Values can be always, hourly, daily, weekly,
     *                                monthly, yearly, never
     * @param   string  $priority   Priority of this item relative to other item on the site. Can be 
     *                                values from 1 to 5 (only numbers!).
     * @return  array   Response array (notice or error)
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
     * Updates the item
     *
     * @access  public
     * @param   int      $id         Item Id
     * @param   int     $parent_id  Parent ID
     * @param   string  $title      Item title
     * @param   string  $shortname  Item shortname (also used in paths)
     * @param   string  $type       Item type (URL, StaticPage, Blog, etc)
     * @param   string  $reference  Type reference (e.g. ID of the static page)
     * @param   string  $change     Change frequency. Values can be always, hourly, daily, weekly,
     *                                monthly, yearly, never
     * @param   string  $priority   Priority of this item relative to other item on the site. Can be 
     *                                values from 1 to 5 (only numbers!).
     * @return  array   Response array (notice or error)
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
     * Deletes the item
     *
     * @access  public
     * @param   int     $id Item ID
     * @return  array   Response array (notice or error)
     */
    function DeleteItem($id)
    {
        $this->_Model->DeleteItem($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Moves item to the given direction
     *
     * @access  public
     * @param   int     $id         Item ID
     * @param   string  $direction  Up or down
     * @return  array   Response array (notice or error)
     */
    function MoveItem($id, $direction)
    {
        $this->_Model->MoveItem($id, $direction);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
    
    /**
     * Sends the sitemap XML URL to search engines
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function PingSitemap()
    {
        $this->gadget->CheckPermission('PingSite');
        $this->_Model->ping(true);
        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_SITEMAP_SENT'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}