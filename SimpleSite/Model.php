<?php
/**
 * SimpleSite Gadget
 *
 * @category   GadgetModel
 * @package    SimpleSite
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SimpleSiteModel extends Jaws_Model
{
    /**
     * Internal variable to load items 
     */
    var $_items;

    /**
     * Return an item
     *
     * @access  public
     * @param   int     $id The ID of the simplesite to return.
     * @return  array   Array that has the properties of a specific simplesite
     */
    function GetItem($id)
    {
        $sql = '
            SELECT [id], [parent_id], [title], [shortname], [rfc_type], [reference], [priority],
                   [changefreq], [rank], [path], [createtime], [updatetime]
            FROM [[simplesite]] 
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SIMPLESITE_ERROR_GET_ITEM'), _t('SIMPLESITE_NAME'));
        }

        return $result;
    }

    /**
     * Get a list (array ) of the simplesite with the items of each one
     *
     * @param   int $levels Show N levels
     * @access  public
     * @return  array   Array that contains all the simplesites with their items
     */
    function GetItems($levels = false)
    {
        if (empty($this->_items)) {
            $this->CreateItemsArray();
        }
 
        if (!$levels) {
            return $this->_items;
        }

        $items = array();

        if ($levels == -1) {
            foreach ($this->_items as $item) {
                $items = array_merge($items, $item['childs']);            
            }
        } elseif ($levels > 0) {
            $items = $this->_items;
            $this->_GetItemLevels($items, 1, $levels);
        }
        return $items;
    }

    /**
     * Returns the given levels depth of items
     * 
     * @param $items Reference to items array
     * @param $current Start
     * @param $depth Depth to return
     * @access private
     */
    function _GetItemLevels(&$items, $current, $depth) 
    {
        foreach ($items as $i) {
            if ($current < $depth) {
                $this->_GetItemLevels($i['childs'], $current + 1, $depth);
            } else {
                $i['childs'] = array();
            }
        }
        return true;
    }

   
    /**
     * Create a hierachical array based on parents... 
     * @access private   
     * @return  boolean  Always true
     */
    function CreateItemsArray()
    {
        $sql = "
            SELECT
                [id], [parent_id], [title], [shortname], [rfc_type], [reference],
                [path], [rank], [changefreq], [priority]
            FROM [[simplesite]] 
            ORDER BY [parent_id], [rank] ";
        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SIMPLESITE_ERROR_GET_ALL_ITEMS'), _t('SIMPLESITE_NAME'));
        }

        foreach ($result as $row) {
            $aux[$row['parent_id']][] = $row;
        }
        $this->_items = array();
        $this->_items = $this->_CreateItemsArray($aux, 0);
        return true;
    }

    /**
     * Create a hierachical array based on parents... recursive proccess
     * @access private
     * @params  array   $items      Array with all the items
     * @params  int     $parent     Parent id to extract
     * @return  array   Childs array
     */
    function _CreateItemsArray(&$items, $parent) 
    {
        $result = array();
        if (isset($items[$parent]) && is_array($items[$parent])) {
            foreach ($items[$parent] as $index=>$item) {
                $result[$index] = $item;
                if ($item['rfc_type'] == 'url') {
                    $result[$index]['url'] = $item['reference'];
                } else {
                    $result[$index]['url'] = $GLOBALS['app']->Map->GetURLFor('SimpleSite', 'Display', array('path' => $item['path']));
                }
                $result[$index]['childs'] = $this->_CreateItemsArray($items, $item['id']);
            }
        }
        return $result;
    }

    /**
     * Returns a single item by title.
     *
     * @access  public
     * @param   string  $title  Item title 
     * @return  array   Array that has the properties of a specific item
     */
    function GetSimpleSiteItemByTitle($title)
    {
        $sql = 'SELECT [id] FROM [[simplesite]] WHERE [title] = {title}';
        $result = $GLOBALS['db']->queryRow($sql, array('title' => $title));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SIMPLESITE_ERROR_GET_ITEM'), _t('SIMPLESITE_NAME'));
        }

        if (!isset($result['id'])) {
            return new Jaws_Error(_t('SIMPLESITE_ERROR_GET_ITEM'), _t('SIMPLESITE_NAME'));
        }

        return $result;
    }

    /**
     * Gets the contents via path
     *
     * @access public
     * @param  string $path     Node path
     * @return string   HTML Contents.
     */
    function GetContent($path)
    {
        // Get type and reference
        $sql = "SELECT [rfc_type], [reference] FROM [[simplesite]] WHERE [path] = {path}";
        $result = $GLOBALS['db']->queryRow($sql, array('path' => $path));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SIMPLESITE_ERROR_GET_ITEM'), _t('SIMPLESITE_NAME'));
        }

        if (!isset($result['rfc_type'])) {
            $result = array('rfc_type' => 'NotFound');
        }

        switch ($result['rfc_type']) {
            case 'StaticPage':
                        $staticPage = $GLOBALS['app']->loadGadget('StaticPage', 'HTML');
                        return $staticPage->Page($result['reference']);
                        break;
            case 'Launcher':
                        $launcher = $GLOBALS['app']->loadGadget('Launcher', 'LayoutHTML');
                        return $launcher->Display($result['reference']);
                        break;
            case 'Blog':
                        $blog = $GLOBALS['app']->loadGadget('Blog', 'HTML');
                        return $blog->SingleView(true, $result['reference']);
                        break;
            default:
                        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
                        return Jaws_HTTPError::Get(404);
                        
        }
    }
    
    /**
     * Create XML struct of sitemap
     *
     * @access  public
     * @param   boolean  $writeToDisk Flag that determinates if content should be written to disk
     * @return  mixed    Returns the XML(string) if it was required, or true
     */
    function makeSitemap($writeToDisk = false)
    {
        $string   = $this->getXMLString();
        $filename = 'sitemap.xml';

        if ($writeToDisk) {
            if (Jaws_Utils::is_writable(JAWS_DATA.'xml/sitemap/')) {
                ///FIXME we need to do more error checking over here
                @file_put_contents(JAWS_DATA . 'xml/sitemap/' . $filename, $string);
                //chmod!
                Jaws_Utils::chmod(JAWS_DATA . 'xml/sitemap/' . $filename);
            }
            return false;
        }
        return $string;
    }

    /**
     * Build the sitemap.xml XML structure
     *
     * @access  public
     * @return  string  XML structure of sitemap.xml
     */
    function getXMLString()
    {
        $xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xmlString.= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        $sql = '
            SELECT
                [id], [parent_id], [title], [reference], [rank], [priority],
                [changefreq], [updatetime]
            FROM [[simplesite]]
            ORDER BY [id], [priority]';

        $result = $GLOBALS['db']->queryAll($sql);
        if (!Jaws_Error::IsError($result)) {
            $date = $GLOBALS['app']->loadDate();
            foreach($result as $row) {
                if (empty($row['reference'])) {
                    continue;
                }
                $reference = $row['reference'];
                if (!preg_match('/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}'
                               .'((:[0-9]{1,5})?\/.*)?$/i' ,$reference)) {
                    $reference = $GLOBALS['app']->getSiteURL('/' . $reference);
                }
                $lastmod   = $date->ToISO($row['updatetime']);
                $reference = htmlentities($reference, ENT_QUOTES, 'UTF-8');
                $reference = str_replace("\n", "", $reference);                
                
                $xmlString.= "<url>\n";
                $xmlString.= "   <loc>".$reference."</loc>\n";
                $xmlString.= "   <lastmod>".$lastmod."</lastmod>\n";
                
                if (!empty($row['changefreq'])) {
                    $xmlString.= "   <changefreq>".$row['changefreq']."</changefreq>\n";
                }
                
                if (is_numeric($row['priority'])) {
                    $xmlString.= "   <priority>".$row['priority']."</priority>\n";
                }
                $xmlString.= "</url>\n";
            }
        }
        $xmlString.= "</urlset>";
        return $xmlString;
    }       
    
    /**
     * Ping the sitemap.xml file to many search engines
     *
     * @access  public
     * @param   boolean  $redo  (Optional) Rewrite sitemap before sending it
     */
    function ping($redo = false)
    {
        if ($redo === true) {
            $buildSitemap = $this->makeSitemap(true);
        }
        
        $url = htmlentities($GLOBALS['app']->Map->GetURLFor('Sitemap', 'GetXML'),
                            ENT_QUOTES,
                            'UTF-8');
        $sengines = array(
                          'http://www.google.com/webmasters/sitemaps/ping?sitemap={local}' => 'get',
                          'http://submissions.ask.com/ping?sitemap={local}' => 'get'
                          );
        
        
        require_once 'HTTP/Request.php';

        $httpRequest = new HTTP_Request();
        foreach($sengines as $engine => $method) {
            $method = strtolower($method);
            if ($method == 'post') {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);
            } else {
                $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
            }
            $engine      = str_replace('local', $url, $engine);
            
            $httpRequest->setURL($engine);
            $resRequest  = $httpRequest->sendRequest();

            if (PEAR::isError($resRequest) || (int) $httpRequest->getResponseCode() <> 200) {
                $GLOBALS['log']->Log(JAWS_LOG_INFO, 'Could not ping sitemap URL to: '.$engine);
            }
        }
        return true;
    }

    /**
     * Returns an array with info of each path element of a given path
     *
     * @variable  $path URL Path
     * @access  public
     * @return  array Array with info of each path element
     */
    function GetBreadcrumb($path)
    {

        $breadcrumb = array();
        $breadcrumb['/'] = _t('SIMPLESITE_HOME');
        $apath = explode('/',$path);
        $a = $this->_items;
        for ($i = 0; $i < count($apath); $i++) {
           for ($j = 0; $j < count($a); $j++) {
               if ($a[$j]['shortname'] == $apath[$i]) {
                   $breadcrumb[$a[$j]['url']] = $a[$j]['title'];
                   $a = $a[$j]['childs'];
                   break;
               }
           }
        }

        return $breadcrumb;
    }

}