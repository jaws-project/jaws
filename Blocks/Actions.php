<?php
/**
 * Blocks Actions file
 *
 * @category   GadgetActions
 * @package    Blocks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/* Normal/Layout actions*/
$actions = array();
$actions['Block'] = array(
    'NormalAction:Block,LayoutAction:Block',
    _t('BLOCKS_SHOW_BLOCK'),
   '',
   true
);
