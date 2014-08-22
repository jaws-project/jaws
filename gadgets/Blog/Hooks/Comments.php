<?php
/**
 * Blog - Comments gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns an array about a blog entry
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   int     $id         Reference id
     * @return  array   entry info
     */
    function Execute($action, $id)
    {
        $entryInfo = array();
        if ($action == 'Post') {
            //Blog model
            $pModel = $this->gadget->model->load('Posts');
            $entry = $pModel->GetEntry($id);

            $url = $this->gadget->urlMap(
                'SingleView',
                array('id' => empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'])
            );
            $entryInfo = array('title' => $entry['title'], 'url' => $url);
        }

        return $entryInfo;
    }
}
