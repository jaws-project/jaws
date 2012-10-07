<?php
/**
 * TMS (Theme Management System) Gadget
 *
 * @category   GadgetInfo
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class TmsInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '0.2.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access private
     */
    var $_IsCore = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'UploadTheme',
        'DownloadTheme'
    );

}