<?php
/**
 * Chatbox Actions file
 *
 * @category   GadgetActions
 * @package    Chatbox
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/* Normal actions*/
$actions = array();

$actions['Post']  = array('NormalAction');

/* Layout actions */
$actions['Display'] = array('LayoutAction', _t('CHATBOX_LAYOUT_DISPLAY'), _t('CHATBOX_LAYOUT_DISPLAY_DESC')); 

/* Admin actions */
$actions['EditEntry'] = array('AdminAction');
$actions['SaveEditEntry'] = array('AdminAction');
$actions['DeleteComment'] = array('AdminAction');
