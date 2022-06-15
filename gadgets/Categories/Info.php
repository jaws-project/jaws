<?php
/**
 * Categories Information
 *
 * @category    GadgetModel
 * @package     Categories
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2017-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Categories_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.2.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Categories';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Categories';

}