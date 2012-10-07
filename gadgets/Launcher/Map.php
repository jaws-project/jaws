<?php
/**
 * Launcher URL maps
 *
 * @category   GadgetMaps
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('DisplayMain', 
                'launcher/{script}', 
                '',
                array ('script' =>'[[:alnum:]]+$')
                );
$maps[] = array('DisplayMain', 
                'launcher/{script}/{params}', 
                '',
                array ('script' =>'[[:alnum:]]+',
                       'params' => '.+')
                );
