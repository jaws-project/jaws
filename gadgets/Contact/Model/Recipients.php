<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Model_Recipients extends Jaws_Gadget_Model
{
    /**
     * Get information of one Recipient
     *
     * @access  public
     * @param   string  $id     ID of the Recipient
     * @return  array  Array with the information of a Recipient or Jaws_Error on failure
     */
    function GetRecipient($id)
    {
        $rcpTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $rcpTable->select('id:integer', 'name', 'email', 'tel', 'fax', 'mobile', 'inform_type:integer', 'visible:integer');
        return $rcpTable->where('id', $id)->fetchRow();
    }

    /**
     * Get a list of the available Recipients
     *
     * @access  public
     * @param   bool    $onlyVisible
     * @param   bool    $limit
     * @param   bool    $offset
     * @return  mixed   Array of Recipients or Jaws_Error on failure
     */
    function GetRecipients($onlyVisible = false, $limit = false, $offset = null)
    {
        $rcpTable = Jaws_ORM::getInstance()->table('contacts_recipients');
        $rcpTable->select('id:integer', 'name', 'email', 'tel', 'fax', 'mobile', 'visible:integer');
        if ($onlyVisible) {
            $rcpTable->where('visible', 1);
        }
        $rcpTable->orderBy('id');
        $rcpTable->limit($limit, $offset);
        return $rcpTable->fetchAll();
    }
}