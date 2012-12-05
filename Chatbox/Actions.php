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
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['EditEntry']     = array('AdminAction');
$admin_actions['SaveEditEntry'] = array('AdminAction');
$admin_actions['DeleteComment'] = array('AdminAction');

$actions['Post']  = array('NormalAction');
$actions['Display'] = array(
    'LayoutAction',
    _t('CHATBOX_LAYOUT_DISPLAY'),
    _t('CHATBOX_LAYOUT_DISPLAY_DESC')
);
