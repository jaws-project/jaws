<?php
/**
 * Blog Gadget
 *
 * @category   GadgetInfo
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.9.0';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddEntries',
        'ModifyOthersEntries',
        'DeleteEntries',
        'PublishEntries',
        'ModifyPublishedEntries',
        'ManageComments',
        'ManageTrackbacks',
        'ManageCategories',
        'Settings',
    );

}