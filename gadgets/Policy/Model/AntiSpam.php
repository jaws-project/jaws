<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_AntiSpam extends Jaws_Gadget_Model
{
    /**
     * Is spam?
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  bool    True if spam otherwise false
     */
    function IsSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_\-]/', '', $this->gadget->registry->fetch('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        return $objFilter->IsSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }
}