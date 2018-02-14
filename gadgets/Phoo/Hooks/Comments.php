<?php
/**
 * Phoo - Comments gadget hook
 *
 * @category   GadgetHook
 * @package    Phoo
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns an array about a phoo entry
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
                $url = $this->gadget->urlMap('Photo', array('photo' => $image['id']));
                $result = array(
                    'reference_title' => $image['title'],
                    'reference_link'  => $url,
                    'author_name'     => '',
                    'author_nickname' => '',
                    'author_email'    => '',
                );
            }
        }

        return $result;
    }

}