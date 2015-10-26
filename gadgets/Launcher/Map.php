<?php
/**
 * Launcher URL maps
 *
 * @category   GadgetMaps
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Execute', 
    'launcher/{script}[/{params}]', 
    array ('script' =>'[[:alnum:]]+', 'params' => '.+')
);
