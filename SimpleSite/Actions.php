<?php
/**
 * SimpleSite Actions file
 *
 * @category   GadgetActions
 * @package    SimpleSite
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();

$index_actions['Display']   = array('NormalAction');
$index_actions['Sitemap']   = array('NormalAction');
/* Standalone actions */
$index_actions['SitemapXML'] = array('StandaloneAction');
/* Layout actions */
$index_actions['Show'] = array(
    'LayoutAction',
    _t('SIMPLESITE_SHOW'),
    _t('SIMPLESITE_SHOW_DESCRIPTION')
);
$index_actions['ShowWithoutTop']  = array(
    'LayoutAction',
    _t('SIMPLESITE_SHOWWITHOUTTOP'),
    _t('SIMPLESITE_SHOWWITHOUTTOP_DESCRIPTION')
);
$index_actions['TopMenu']         = array(
    'LayoutAction',
    _t('SIMPLESITE_TOPMENU'),
    _t('SIMPLESITE_TOPMENU_DESCRIPTION')
);
$index_actions['ShowTwoLevels']   = array(
    'LayoutAction',
    _t('SIMPLESITE_TWOLEVELS'),
    _t('SIMPLESITE_TWOLEVELS_DESCRIPTION')
);
$index_actions['ShowThreeLevels'] = array(
    'LayoutAction',
    _t('SIMPLESITE_THREELEVELS'),
    _t('SIMPLESITE_THREELEVELS_DESCRIPTION')
);
$index_actions['DisplayLevel'] = array(
    'LayoutAction',
    _t('SIMPLESITE_DISPLAYLEVEL'),
    _t('SIMPLESITE_DISPLAYLEVEL_DESCRIPTION')
);
$index_actions['Breadcrumb'] = array(
    'LayoutAction',
    _t('SIMPLESITE_BREADCRUMB'),
    _t('SIMPLESITE_BREADCRUMB_DESCRIPTION')
);
