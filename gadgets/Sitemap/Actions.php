<?php
/**
 * Sitemap Actions file
 *
 * @category    GadgetActions
 * @package     Sitemap
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Display'] = array(
    'normal' => true,
    'file'   => 'Sitemap'
);
$actions['Sitemap'] = array(
    'normal' => true,
    'file'   => 'Sitemap'
);
$actions['SitemapXML'] = array(
    'standalone' => true,
    'file'   => 'Sitemap'
);
$actions['Show'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['ShowWithoutTop'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['TopMenu'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['ShowTwoLevels'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['ShowThreeLevels'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['DisplayLevel'] = array(
    'layout' => true,
    'file'   => 'Show'
);
$actions['Breadcrumb'] = array(
    'layout' => true,
    'file'   => 'Breadcrumb'
);

/**
 * Admin actions
 */
$admin_actions['ManageSitemap'] = array(
    'normal' => true,
    'file'   => 'ManageSitemap',
);
$admin_actions['Sitemap_Actions_Admin_Ajax'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetItems'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetReferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetStaticPageReferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetBlogReferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetLauncherReferences'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['NewItem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateItem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteItem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MoveItem'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PingSitemap'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
