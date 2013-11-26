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
class Sitemap_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Sitemap_Actions_Admin_Ajax($gadget)
    {
        parent::__construct($gadget);
        $this->_Model = $this->gadget->model->loadAdmin('Sitemap');
    }

    /**
     * Get Gadget Categories List
     *
     * @access  public
     * @return  array   Group information array
     */
    function GetCategoriesList()
    {
        @list($gadget) = jaws()->request->fetchAll('post');
        $action = $this->gadget->action->loadAdmin('ManageSitemap');
        return $action->GetCategoriesList($gadget);
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
     * @access   public
     * @internal param  string  $type   Type of references
     * @return   mixed  Array of references or false
     */
    function GetReferences()
    {
        @list($type) = jaws()->request->fetchAll('post');
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
        $staticPage = Jaws_Gadget::getInstance('StaticPage')->model->load('Page');
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
        $blog = Jaws_Gadget::getInstance('Blog')->model->load('Posts');
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
        $launcher = Jaws_Gadget::getInstance('Launcher')->model->load('Scripts');
        $items = $launcher->GetScripts();
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
     * @access   public
     * @internal param  int     $parent_id      Parent ID
     * @internal param  string  $title Item     title
     * @internal param  string  $shortname      Item shortname (also used in paths)
     * @internal param  string  $type           Item type (URL, StaticPage, Blog, etc)
     * @internal param  string  $reference      Type reference (e.g. ID of the static page)
     * @internal param  string  $change         Change frequency. Values can be always, hourly, daily, weekly,
     *                                          monthly, yearly, never
     * @internal param  string  $priority       Priority of this item relative to other item on the site. Can be
     *                                          values from 1 to 5 (only numbers!).
     * @return   array  Response array (notice or error)
     */
    function NewItem()
    {
        @list($parent_id, $title, $shortname, $type, $reference, $change, $priority) = jaws()->request->fetchAll('post');
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
     * @access   public
     * @internal param  int     $id             Item Id
     * @internal param  int     $parent_id      Parent ID
     * @internal param  string  $title          Item title
     * @internal param  string  $shortname      Item shortname (also used in paths)
     * @internal param  string  $type           Item type (URL, StaticPage, Blog, etc)
     * @internal param  string  $reference      Type reference (e.g. ID of the static page)
     * @internal param  string  $change         Change frequency. Values can be always, hourly, daily, weekly,
     *                                          monthly, yearly, never
     * @internal param  string  $priority       Priority of this item relative to other item on the site. Can be
     *                                          values from 1 to 5 (only numbers!).
     * @return   array  Response array (notice or error)
     */
    function UpdateItem()
    {
        @list($id, $parent_id, $title, $shortname, $type, $reference, $change, $priority) = jaws()->request->fetchAll('post');
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
     * @access   public
     * @internal param  int     $id     Item ID
     * @return   array  Response array (notice or error)
     */
    function DeleteItem()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $this->_Model->DeleteItem($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Moves item to the given direction
     *
     * @access   public
     * @internal param  int     $id         Item ID
     * @internal param  string  $direction  Up or down
     * @return   array  Response array (notice or error)
     */
    function MoveItem()
    {
        @list($id, $direction) = jaws()->request->fetchAll('post');
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
        $model =  $this->gadget->model->load('Ping');
        $model->ping(true);
        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_SITEMAP_SENT'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}