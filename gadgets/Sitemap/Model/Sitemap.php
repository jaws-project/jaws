<?php
/**
 * Sitemap Gadget
 *
 * @category    GadgetModel
 * @package     Sitemap
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2006-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Model_Sitemap extends Jaws_Gadget_Model
{
    /**
     * Gets list of gadgets that have Sitemap
     *
     * @access  public
     * @return  array   List of gadgets
     */
    function GetAvailableSitemapGadgets()
    {
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList(false, true, true);
        $gadgets = array();
        foreach ($gadgetList as $key => $gadget) {
            if (is_file(JAWS_PATH . 'gadgets/' . $gadget['name'] . '/Hooks/Sitemap.php')) {
                $gadget['name'] = trim($gadget['name']);
                if ($gadget['name'] == 'Sitemap' || empty($gadget['name'])) {
                    continue;
                }

                $gadgets[$key] = $gadget;
            }
        }
        return $gadgets;
    }

    /**
     * Get a gadget properties
     *
     * @access  public
     * @param   string  $gadget
     * @return  array   Category data
     */
    function GetGadgetProperties($gadget)
    {
        return unserialize($this->gadget->registry->fetch($gadget));
    }

    /**
     * Get a gadget categories properties
     *
     * @access  public
     * @param   string  $gadget
     * @return  array   Category data
     */
    function GetGadgetCategoryProperties($gadget)
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $sitemapTable->select('id:integer', 'gadget', 'category', 'priority:float','frequency', 'status');
        $rows = $sitemapTable->where('gadget', $gadget)->fetchAll();
        if (Jaws_Error::IsError($rows)) {
            return false;
        }

        $result = array();
        foreach($rows as $row) {
            $result[$row['category']] = $row;
        }

        return $result;
    }

    /**
     * Get Sitemap XML content
     *
     * @access  public
     * @return  XML   content of sitemap
     */
    function GetSitemapXML()
    {
        $xml_file = JAWS_DATA . 'sitemap/sitemap.xml';
        if (file_exists($xml_file)) {
            if (false === $data = @file_get_contents($xml_file)) {
                return false;
            }
            return $data;
        }

        $tpl = $this->gadget->template->load('SitemapXML.html');
        $tpl->SetBlock('xml');
        $date = Jaws_Date::getInstance();

        $gadgets = $this->GetAvailableSitemapGadgets();
        foreach ($gadgets as $gadget) {
            $gadget_xml_file = JAWS_DATA. 'sitemap/'. strtolower($gadget['name']). '/sitemap.xml';
            if (file_exists($gadget_xml_file)) {
                $tpl->SetBlock('xml/item');
                $tpl->SetVariable('loc', $this->gadget->urlMap(
                                    'SitemapXML',
                                    array('gname' => strtolower($gadget['name'])), true));
                $tpl->SetVariable('lastmod', $date->ToISO(filemtime($gadget_xml_file)));
                $tpl->ParseBlock('xml/item');
            }

        }

        $tpl->ParseBlock('xml');
        $xmlContent = $tpl->Get();
        if (!Jaws_Utils::file_put_contents($xml_file, $xmlContent)) {
            return false;
        }

        return $xmlContent;
    }

    /**
     * Get a gadget Sitemap XML content
     *
     * @access  public
     * @param   string  $gadget   Gadget name
     * @return  XML     content of sitemap
     */
    function GetGadgetSitemapXML($gadget)
    {
        $xml_file = JAWS_DATA . 'sitemap/'. strtolower($gadget). '/sitemap.xml';
        if (file_exists($xml_file)) {
            if (false === $data = @file_get_contents($xml_file)) {
                return false;
            }
            return $data;
        }
    }

    /**
     * Get Sitemap data
     *
     * @access  public
     * @param   string  $gadget   Gadget name
     * @return  array   sitemap data
     */
    function GetSitemapData($gadget)
    {
        $data_file = JAWS_DATA . 'sitemap/'. strtolower($gadget) . '/sitemap.bin';
        if (file_exists($data_file)) {
            if (false === $data = @file_get_contents($data_file)) {
                return array();
            }
            return unserialize($data);
        }
    }
}