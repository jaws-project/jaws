<?php
/**
 * Blog - Comments gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns an array about a blog entry
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   int     $reference  Reference id
     * @return  array   entry info
     */
    function Execute($action, $reference)
    {
        $result = array();
        if ($action == 'Post') {
            $pModel = $this->gadget->model->load('Posts');
            $post = $pModel->GetEntry($reference);
            if (!Jaws_Error::IsError($post) && !empty($post)) {
                $url = $this->gadget->urlMap(
                    'SingleView',
                    array('id' => empty($post['fast_url']) ? $post['id'] : $post['fast_url'])
                );
                $result = array(
                    'title' => $post['title'],
                    'url' => $url
                );
            }
        }

        return $result;
    }

}