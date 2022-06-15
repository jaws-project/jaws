<?php
/**
 * Policy URL maps
 *
 * @category   GadgetMaps
 * @package    Policy
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'Captcha',
    'captcha/{field}/{key}',
    array(
        'field' => '[[:alnum:]\-_]+',
        'key' => '[[:alnum:]]+',
    ),
    '',
);
