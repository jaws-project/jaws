<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Admin_Comments extends Jaws_Gadget_Model
{
    /**
     * Update a post comments count
     *
     * @access  public
     * @param   int     $id              Post id.
     * @param   int     $commentCount    How Many comment
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdatePostCommentsCount($id, $commentCount)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        return $blogTable->update(array('comments'=>$commentCount))->where('id', $id)->exec();
    }

    /**
     * Delete all comments in a given entry
     *
     * @access  public
     * @param   int     $id         Post id.
     */
    function DeleteCommentsIn($id)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('DeleteComments');
        return $cModel->DeleteGadgetComments($this->gadget->name, $id);
    }

}