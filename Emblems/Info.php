<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetInfo
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class EmblemsInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '0.8.0';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddEmblem',
        'EditEmblem',
        'DeleteEmblem',
        'UpdateProperties',
    );

}