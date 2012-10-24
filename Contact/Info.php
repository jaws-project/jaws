<?php
/**
 * Contact gadget info
 *
 * @category   GadgetInfo
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ContactInfo extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.3.4';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ManageContacts',
        'EditSentMessage',
        'ManageRecipients',
        'AccessToMailer',
        'UpdateProperties',
        'AllowAttachment',
    );

}