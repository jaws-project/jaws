<?php
/**
 * Webcam Actions file
 *
 * @category   GadgetActions
 * @package    Webcam
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/* Normal actions*/
$actions = array();
/* Layout actions */
$actions['Display'] = array('LayoutAction', 
                            _t('WEBCAM_LAYOUT_DISPLAY'),
                            _t('WEBCAM_LAYOUT_DISPLAY_DESC'));

$actions['Random'] = array('LayoutAction', 
                           _t('WEBCAM_LAYOUT_RANDOM'),
                           _t('WEBCAM_LAYOUT_RANDOM_DESC'));
?>
