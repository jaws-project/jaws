<?php
/**
 * Sitemap Actions file
 *
 * @category    GadgetActions
 * @package     Sitemap
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['SitemapXML'] = array(
    'standalone' => true,
    'file'   => 'Sitemap'
);
$actions['Sitemap'] = array(
    'normal' => true,
    'file'   => 'Sitemap'
);
$actions['Robots'] = array(
    'standalone' => true,
    'file'   => 'Robots',
);

/**
 * Admin actions
 */
$admin_actions['ManageSitemap'] = array(
    'normal' => true,
    'file'   => 'ManageSitemap',
);
$admin_actions['GetCategoriesList'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['GetGadgetUI'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['GetCategoryUI'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['GetCategory'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['GetGadget'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['UpdateCategory'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['UpdateGadgetProperties'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['SyncSitemapXML'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['SyncSitemapData'] = array(
    'standalone' => true,
    'file' => 'ManageSitemap',
);
$admin_actions['PingSearchEngines'] = array(
    'standalone' => true,
    'file' => 'Ping',
);
$admin_actions['Robots'] = array(
    'normal' => true,
    'file'   => 'Robots',
);
$admin_actions['UpdateRobots'] = array(
    'standalone' => true,
    'file'   => 'Robots',
);
