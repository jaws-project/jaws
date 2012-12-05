<?php
/**
 * Emblems Gadget
 *
 * @category   Gadget
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions*/
$admin_actions['AddEmblem']  = array('AdminAction');
$admin_actions['EditEmblem'] = array('AdminAction');

/* Layout actions */
$index_actions['Display'] = array(
    'LayoutAction',
    _t('EMBLEMS_ACTION_DISPLAY'),
    _t('EMBLEMS_ACTION_DISPLAY_DESC')
);
