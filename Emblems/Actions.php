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
$actions = array();

/* Normal actions*/

/* Layout actions */
$actions['Display'] = array('LayoutAction',
                            _t('EMBLEMS_ACTION_DISPLAY'),
                            _t('EMBLEMS_ACTION_DISPLAY_DESC'));

/* Admin actions*/
$actions['AddEmblem']  = array('AdminAction');
$actions['EditEmblem'] = array('AdminAction');
?>
