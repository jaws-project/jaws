<?php
/**
 * Simplesite URL maps
 *
 * @category   GadgetMaps
 * @package    SimpleSite
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('Sitemap',    'sitemap');
$maps[] = array('SitemapXML', 'sitemap/xml');
$maps[] = array('Display',
                'contents/{path}',
                '',
                array('path' => '.+')
                );
