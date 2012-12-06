<?php
/**
 * SimpleSite Actions file
 *
 * @category    GadgetActions
 * @package     SimpleSite
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Display']   = array('NormalAction');
$actions['Sitemap']   = array('NormalAction');
/* Standalone actions */
$actions['SitemapXML'] = array('StandaloneAction');
/* Layout actions */
$actions['Show'] = array(
    'LayoutAction',
    _t('SIMPLESITE_SHOW'),
    _t('SIMPLESITE_SHOW_DESCRIPTION')
);
$actions['ShowWithoutTop']  = array(
    'LayoutAction',
    _t('SIMPLESITE_SHOWWITHOUTTOP'),
    _t('SIMPLESITE_SHOWWITHOUTTOP_DESCRIPTION')
);
$actions['TopMenu']         = array(
    'LayoutAction',
    _t('SIMPLESITE_TOPMENU'),
    _t('SIMPLESITE_TOPMENU_DESCRIPTION')
);
$actions['ShowTwoLevels']   = array(
    'LayoutAction',
    _t('SIMPLESITE_TWOLEVELS'),
    _t('SIMPLESITE_TWOLEVELS_DESCRIPTION')
);
$actions['ShowThreeLevels'] = array(
    'LayoutAction',
    _t('SIMPLESITE_THREELEVELS'),
    _t('SIMPLESITE_THREELEVELS_DESCRIPTION')
);
$actions['DisplayLevel'] = array(
    'LayoutAction',
    _t('SIMPLESITE_DISPLAYLEVEL'),
    _t('SIMPLESITE_DISPLAYLEVEL_DESCRIPTION')
);
$actions['Breadcrumb'] = array(
    'LayoutAction',
    _t('SIMPLESITE_BREADCRUMB'),
    _t('SIMPLESITE_BREADCRUMB_DESCRIPTION')
);
