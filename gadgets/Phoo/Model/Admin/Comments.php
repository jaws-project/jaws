<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Comments extends Jaws_Gadget_Model
{
    /**
     * Update an image comments count
     *
     * @access  public
     * @param   int     $id              Image id.
     * @param   int     $commentCount    How Many comment?
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdateImageCommentsCount($id, $commentCount)
    {
        $phooTable = Jaws_ORM::getInstance()->table('phoo_image');
        return $phooTable->update(array('comments'=>$commentCount))->where('id', $id)->exec();
    }

}