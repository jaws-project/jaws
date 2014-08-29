<?php
/**
 * Phoo - Comments gadget hook
 *
 * @category   GadgetHook
 * @package    Phoo
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Comments extends Jaws_Gadget_Hook
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
        if ($action == 'Image') {
            $model = $this->gadget->model->load('Photos');
            $image = $model->GetImageEntry($reference);
            if (!Jaws_Error::IsError($image) && !empty($image)) {
                $url = $this->gadget->urlMap('ViewImage', array('id' => $image['id']));
                $result = array(
                    'title' => $image['title'],
                    'url' => $url
                );
            }
        }

        return $result;
    }

}