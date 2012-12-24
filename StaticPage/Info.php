<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetInfo
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.8.4';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddPage',
        'EditPage',
        'DeletePage',
        'PublishPages',
        'ManagePublishedPages',
        'ModifyOthersPages',
        'ManageGroups',
        'Properties'
    );

}