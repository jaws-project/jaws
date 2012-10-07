<?php
/**
 * VisitCounter Gadget
 *
 * @category   GadgetInfo
 * @package    VisitCounter
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounterInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '0.8.2';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ResetCounter',
        'CleanEntries',
        'UpdateProperties'
    );

}