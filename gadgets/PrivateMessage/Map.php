<?php
/**
 * PrivateMessage URL maps
 *
 * @category   GadgetMaps
 * @package    PrivateMessage
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array('Inbox', 'pm/inbox');
$maps[] = array('Outbox', 'pm/outbox');
$maps[] = array('Send', 'pm/send');
$maps[] = array(
    'ViewMessage',
    'pm/message/{id}',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
