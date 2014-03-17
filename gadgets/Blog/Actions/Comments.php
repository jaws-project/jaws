<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Comments extends Blog_Actions_Default
{

    /**
     * Displays a preview of the given blog comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        $id = (int)jaws()->request->fetch('reference', 'post');
        $model = $this->gadget->model->load('Posts');
        $entry = $model->GetEntry($id, true);
        if (Jaws_Error::isError($entry)) {
            $GLOBALS['app']->Session->PushSimpleResponse($entry->getMessage(), 'Blog');
            Jaws_Header::Location($this->gadget->urlMap('DefaultAction'));
        }

        $postHTML = $this->gadget->action->load('Post');
        $id = !empty($entry['fast_url']) ? $entry['fast_url'] : $entry['id'];
        return $postHTML->SingleView($id, true);
    }

}