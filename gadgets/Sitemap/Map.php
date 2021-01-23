<?php
/**
 * Sitemap URL maps
 *
 * @category   GadgetMaps
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'SitemapXML',
    'sitemap[/{gname}]',
    array('gname' => '[[:lower:]\-]+'),
    'xml'
);
$maps[] = array('Sitemap', 'sitemap');
$maps[] = array(
    'Robots',
    'robots',
    array(),
    'txt'
);