<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Model_Links extends Jaws_Gadget_Model
{
    /**
     * Get information about a link
     *
     * @access  public
     * @param   int     $id     The links id
     * @return  mixed   An array contains link information and Jaws_Error on error
     */
    function GetLink($id)
    {
        $objORM = Jaws_ORM::getInstance()->table('linkdump_links');
        $objORM->select(
            'id:integer','gid:integer', 'title', 'description', 'url', 'fast_url', 'createtime', 'updatetime',
            'clicks:integer', 'rank:integer'
        );

        $objORM->where(is_numeric($id)? 'id' : 'fast_url', $id);
        $link = $objORM->fetchRow();
        if (Jaws_Error::IsError($link)) {
            return $link;
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            if (!empty($link)) {
                $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tags = $model->GetItemTags(array('gadget' => 'LinkDump', 'action' => 'link', 'reference' => $id), true);
                $link['tags'] = array_filter($tags);
            }
        }

        return $link;
    }

    /**
     * Increase the link's clicks by one
     *
     * @access  public
     * @param   int     $id     Link's id
     * @return  mixed   True on Success and Jaws_Error otherwise
     */
    function Click($id)
    {
        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        return $linksTable->update(array('clicks' => $linksTable->expr('clicks + ?', 1)))->where('id', $id)->exec();
    }
}