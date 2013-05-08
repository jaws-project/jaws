<?php
/**
 * Main methods of Ajax services
 *
 * @category   Ajax
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Ajax extends Jaws_Gadget
{
    /**
     * Model
     *
     * @access  private
     * @var     Jaws_Model
     */
    var $_Model;

    /**
     * Constructor
     *
     * @access  public
     * @param   object  $model  Jaws_Model reference
     * @return  void
     */
    function Jaws_Gadget_Ajax(&$model)
    {
        $this->_Model =& $model;
    }

}