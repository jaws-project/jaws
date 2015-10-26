<?php
/**
 * Faq Gadget
 *
 * @category   GadgetInfo
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.9.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'View';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'ManageQuestions';

}