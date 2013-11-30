<?php
/**
 * Sitemap Gadget
 *
 * @category   GadgetModel
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Model_Admin_Sitemap extends Sitemap_Model_Sitemap
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
            if (is_file(JAWS_PATH . 'gadgets/' . $gadget['name'] . '/hooks/Sitemap.php')) {
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
     * Get a gadget category properties
     *
     * @access  public
     * @param   string  $gadget
     * @param   string  $category
     * @return  array   Category data
     */
    function GetCategoryProperties($gadget, $category)
    {
        $sitemapTable = Jaws_ORM::getInstance()->table('sitemap');
        $sitemapTable->select('id:integer', 'gadget', 'category', 'priority:float', 'frequency', 'status');
        $sitemapTable->where('gadget', $gadget);
        return $sitemapTable->and()->where('category', $category)->fetchRow();
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
            $result[$row['id']] = $row;
        }

        return $result;
    }

    /**
     * Update a category properties
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $category   Category name
     * @param   array   $data       Sitemap properties
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function UpdateCategory($gadget, $category, $data)
    {
        // check for exiting category properties in DB
        $categoryProperties = $this->GetCategoryProperties($gadget, $category);
        if(empty($categoryProperties)) {
            // Add new record to DB
            $table = Jaws_ORM::getInstance()->table('sitemap');
            $data['gadget'] = $gadget;
            $data['category'] = $category;
            $result = $table->insert($data)->exec();
        } else {
            // Update exiting record in DB
            $table = Jaws_ORM::getInstance()->table('sitemap');
            $result = $table->update($data)->where('gadget', $gadget)->and()->where('category', $category)->exec();
        }

        return $result;
    }

    /**
     * Update a gadget properties
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   array   $data       Sitemap properties
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function UpdateGadget($gadget, $data)
    {
        if($this->gadget->registry->fetch($gadget)==null) {
            return $this->gadget->registry->insert($gadget, serialize($data));
        } else {
            return $this->gadget->registry->update($gadget, serialize($data));
        }
    }

    /**
     * Sync sitemap XML files
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function SyncSitemapXML($gadget)
    {
        $tpl = $this->gadget->template->loadAdmin('GadgetXML');
        $tpl->SetBlock('xml');

        // Fetch default sitemap config from registry
        $defaultPriority = (int)$this->gadget->registry->fetch('sitemap_default_priority');
        $defaultFrequency = (int)$this->gadget->registry->fetch('sitemap_default_frequency');

        // Fetch gadget sitemap config
        $gadgetProperties = $this->GetGadgetProperties($gadget);
        $gadgetPriority = null;
        $gadgetFrequency = null;
        if (!empty($gadgetProperties)) {
            $gadgetPriority = $gadgetProperties['priority'];
            $gadgetFrequency = $gadgetProperties['frequency'];
        }

        $frequencyArray = array(
            1 => 'always',
            2 => 'hourly',
            3 => 'daily',
            4 => 'weekly',
            5 => 'monthly',
            6 => 'yearly',
            7 => 'never'
        );

        $objGadget = Jaws_Gadget::getInstance($gadget);
        if (Jaws_Error::IsError($objGadget)) {
            return '';
        }
        $objHook = $objGadget->hook->load('Sitemap');
        if (Jaws_Error::IsError($objHook)) {
            return '';
        }

        $allItems = $objHook->Execute(2);
        if (Jaws_Error::IsError($allItems) || empty($allItems)) {
            return '';
        }

        $allCategories = $objHook->Execute(1);
        $gadgetCategories = $this->GetGadgetCategoryProperties($gadget);
        $finalCategory = array();
        foreach($allCategories as $cat) {
            $property = array();
            if(isset($gadgetCategories[$cat['id']])) {
                $property['priority'] = $gadgetCategories[$cat['id']]['priority'];
                $property['frequency'] = $gadgetCategories[$cat['id']]['frequency'];

            } else {
                $property['priority'] = $defaultPriority;
                $property['frequency'] = $defaultFrequency;
            }
//            $property[] = '';
            $finalCategory[$cat['id']] = $property;
        }

        foreach ($allItems as $item) {
            $tpl->SetBlock('xml/item');
            $tpl->SetVariable('loc', $item['url']);
            if(!empty($category['lastmod'])) {
                $tpl->SetBlock('xml/item/lastmod');
                $tpl->SetVariable('lastmod', $item['lastmod']);
                $tpl->ParseBlock('xml/item/lastmod');
            }

            // Frequency
            $frequency = null;
            if (!empty($item['parent'])) {
                $frequency = $finalCategory[$item['parent']]['frequency'];
            }
            if (empty($frequency)) {
                $frequency = $gadgetFrequency;
            }
            if (empty($frequency)) {
                $frequency = $defaultFrequency;
            }
            if (!empty($frequency)) {
                $tpl->SetBlock('xml/item/changefreq');
                $tpl->SetVariable('changefreq', $frequencyArray[$frequency]);
                $tpl->ParseBlock('xml/item/changefreq');
            }

            // Priority
            $priority = null;
            if (!empty($item['parent'])) {
                $priority = $finalCategory[$item['parent']]['priority'];
            }
            if ($priority == null) {
                $priority = $gadgetPriority;
            }
            if ($priority == null) {
                $priority = $defaultPriority;
            }

            if ($priority != null) {
                $tpl->SetBlock('xml/item/priority');
                $tpl->SetVariable('priority', $priority);
                $tpl->ParseBlock('xml/item/priority');
            }

            $tpl->ParseBlock('xml/item');
        }

        $tpl->ParseBlock('xml');
        $xmlContent = $tpl->Get();

        // Check gadget directory in sitemap
        $gadget_dir = JAWS_DATA . 'sitemap' . DIRECTORY_SEPARATOR . $gadget . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($gadget_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $gadget_dir), _t('SITEMAP_NAME'));
        }

        $cache_file = $gadget_dir . 'sitemap.xml';
        if (!Jaws_Utils::file_put_contents($cache_file, $xmlContent)) {
            return false;
        }
        return true;
    }

    /**
     * Sync sitemap data files
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  mixed   Array of Tag info or Jaws_Error on failure
     */
    function SyncSitemapData($gadget)
    {
        $objGadget = Jaws_Gadget::getInstance($gadget);
        if (Jaws_Error::IsError($objGadget)) {
            return '';
        }
        $objHook = $objGadget->hook->load('Sitemap');
        if (Jaws_Error::IsError($objHook)) {
            return '';
        }

        $result[$gadget] = array();
        $gResult = $objHook->Execute(1);
        if (Jaws_Error::IsError($gResult) || empty($gResult)) {
            return '';
        }

        // Check gadget directory in sitemap
        $gadget_dir = JAWS_DATA . 'sitemap' . DIRECTORY_SEPARATOR . $gadget . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($gadget_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $gadget_dir), _t('SITEMAP_NAME'));
        }

        $cache_file = $gadget_dir . 'data.bin';
        if (!Jaws_Utils::file_put_contents($cache_file, serialize($gResult))) {
            return false;
        }
        return true;
    }
}