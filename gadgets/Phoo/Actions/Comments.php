<?php
/**
 * Phoo Gadget
 *
 * @category    Gadget
 * @package     Phoo
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Comments extends Jaws_Gadget_Action
{
    /**
     * Displays a preview of the given photo comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        $post = jaws()->request->fetch(array('reference', 'albumid'), 'post');
        $post['albumid']   = (int)$post['albumid'];
        $post['reference'] = (int)$post['reference'];

        $model = $this->gadget->model->load('Photos');
        $image = $model->GetImage($post['reference'], $post['albumid']);
        if (Jaws_Error::isError($image)) {
            $GLOBALS['app']->Session->PushSimpleResponse($image->getMessage(), $this->gadget->name);
            Jaws_Header::Location($this->gadget->urlMap('AlbumList'));
        }

        $pActions = $this->gadget->action->load('Photos');
        return $pActions->ViewImage($post['reference'], $post['albumid'], true);
    }

}