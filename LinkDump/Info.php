<?php
/**
 * LinnkDump Gadget
 *
 * @category   GadgetInfo
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amir@iranamp.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDumpInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.4.4';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ManageLinks',
        'ManageGroups',
        'ManageTags',
        'UpdateProperties',
    );

}