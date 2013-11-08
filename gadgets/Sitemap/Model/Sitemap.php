<?php
/**
 * Sitemap Gadget
 *
 * @category   GadgetModel
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Model_Sitemap extends Jaws_Gadget_Model
{

    /**
     * Internal variable to load items
     *
     * @var     array
     * @access  private
     */
    var $_items;

    /**
     * Returns an item
     *
     * @access  public
     * @param   int     $id The ID of the sitemap to return
     * @return  array   Array that has the properties of a specific sitemap
     */
    function GetItem($id)
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $result = $sitemapTable->select(
            'id:integer', 'parent_id:integer', 'title', 'shortname', 'rfc_type', 'reference', 'priority:float',
            'changefreq', 'rank:integer', 'path', 'createtime', 'updatetime'
        )->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SITEMAP_ERROR_GET_ITEM'), _t('SITEMAP_NAME'));
        }

        return $result;
    }

    /**
     * Gets list of sitemaps with their items
     *
     * @access  public
     * @param   int     $levels Displays N levels
     * @return  array   Array that contains all the sitemaps with their items
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
     * @access  private
     * @param   array       $items      Reference to items array
     * @param   int         $current    Start
     * @param   int         $depth      Depth to return
     * @return  bool        Always true
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
     * Creates a hierachical array based on parents...
     *
     * @access  private
     * @return  bool    Always true
     */
    function CreateItemsArray()
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $result = $sitemapTable->select(
            'id:integer', 'parent_id:integer', 'title', 'shortname', 'rfc_type', 'reference', 'priority:float',
            'changefreq', 'rank:integer', 'path'
        )->orderBy('parent_id', 'rank')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SITEMAP_ERROR_GET_ALL_ITEMS'), _t('SITEMAP_NAME'));
        }

        foreach ($result as $row) {
            $aux[$row['parent_id']][] = $row;
        }
        $this->_items = array();
        $this->_items = $this->_CreateItemsArray($aux, 0);
        return true;
    }

    /**
     * Creates a hierachical array based on parents... recursive proccess
     *
     * @access  private
     * @params  array   $items      Reference to items array
     * @params  int     $parent     Parent ID to extract
     * @return  array   Children array
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
                    $result[$index]['url'] = $GLOBALS['app']->Map->GetURLFor('Sitemap', 'Display', array('path' => $item['path']));
                }
                $result[$index]['childs'] = $this->_CreateItemsArray($items, $item['id']);
            }
        }
        return $result;
    }

    /**
     * Returns a single item by title
     *
     * @access  public
     * @param   string  $title  Item title
     * @return  array   Array of item properties
     */
    function GetSitemapItemByTitle($title)
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $result = $sitemapTable->select('id:integer')->where('title', $title)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SITEMAP_ERROR_GET_ITEM'), _t('SITEMAP_NAME'));
        }

        if (!isset($result['id'])) {
            return new Jaws_Error(_t('SITEMAP_ERROR_GET_ITEM'), _t('SITEMAP_NAME'));
        }

        return $result;
    }

    /**
     * Gets the content via path
     *
     * @access  public
     * @param   string $path    Node path
     * @return  string  XHTML content
     */
    function GetContent($path)
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $result = $sitemapTable->select('rfc_type', 'reference')->where('path', $path)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('SITEMAP_ERROR_GET_ITEM'), _t('SITEMAP_NAME'));
        }

        if (!isset($result['rfc_type'])) {
            $result = array('rfc_type' => 'NotFound');
        }

        switch ($result['rfc_type']) {
            case 'StaticPage':
                $staticPage = Jaws_Gadget::getInstance('StaticPage')->action->load('Page');
                return $staticPage->Page($result['reference']);
                break;
            case 'Launcher':
                $launcher = Jaws_Gadget::getInstance('Launcher')->action->load('Execute');
                return $launcher->Execute($result['reference']);
                break;
            case 'Blog':
                $blog = Jaws_Gadget::getInstance('Blog')->action->load('Default');
                return $blog->SingleView(true, $result['reference']);
                break;
            default:
                return Jaws_HTTPError::Get(404);

        }
    }

    /**
     * Creates XML struct of sitemap
     *
     * @access  public
     * @param   bool    $writeToDisk Flag that determinate if content should be written to disk
     * @return  mixed   XML content if it is required, or true
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
     * Builds the XML structure(sitemap.xml)
     *
     * @access  public
     * @return  string  XML structure of sitemap.xml
     */
    function getXMLString()
    {
        $xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xmlString.= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $result = $sitemapTable->select(
            'id:integer', 'parent_id:integer', 'title', 'reference', 'rank:integer', 'priority:float',
            'changefreq',  'updatetime'
        )->orderBy('id', 'priority')->fetchAll();

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
}