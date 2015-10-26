<?php
/**
 * Initiates Piwi Project.
 *
 * @category   Application
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
if (!Jaws::classExists('Piwi')) {
    if (!defined('PIWI_URL')) {
        define('PIWI_URL', 'libraries/piwi/');
    }
    if (!defined('PIWI_CREATE_PIWIXML')) {
        define('PIWI_CREATE_PIWIXML', 'no');
    }

    if (!defined('PIWI_LOAD')) {
        define('PIWI_LOAD', 'SMART');
    }

    require JAWS_PATH . 'libraries/piwi/Piwi.php';

    $config = array(
                'LINK_PRIFIX'                => '',
                'DATAGRID_ACTION_LABEL'      => _t('GLOBAL_ACTIONS'),
                'DATAGRID_PAGER_PAGEBY'      => 10,
                'DATAGRID_PAGER_MODE'        => 'PIWI_PAGER_NORMAL',
                'CLASS_ODD'                  => 'piwi_option_odd',
                'CLASS_EVEN'                 => 'piwi_option_even',
                'DATAGRID_CLASS_CSS'         => 'jawsDatagrid',
                'DATAGRID_PAGER_LABEL_FIRST' => _t('GLOBAL_FIRST'),
                'DATAGRID_PAGER_LABEL_PREV'  => _t('GLOBAL_PREVIOUS'),
                'DATAGRID_PAGER_LABEL_NEXT'  => _t('GLOBAL_NEXT'),
                'DATAGRID_PAGER_LABEL_LAST'  => _t('GLOBAL_LAST'),
                'PIWI_NAME_AS_ID'            => true,
            );
    Piwi::exportConf($config);
}
