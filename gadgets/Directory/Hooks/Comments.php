<?php
/**
 * Directory - Comments gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 */
class Directory_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns comments for specific file
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   int     $reference  Reference id
     * @return  array   entry info
     */
    function Execute($action, $reference)
    {
        $result = array();
        if ($action == 'File') {
            $fModel = $this->gadget->model->load('Files');
            $file = $fModel->GetFile($reference);
            if (!Jaws_Error::IsError($file) && !empty($file)) {
                $url = $this->gadget->urlMap('Directory', array('id' => $file['id']));
                $result = array(
                    'title' => $file['title'],
                    'url'   => $url,
                    'author_name'     => '',
                    'author_nickname' => '',
                    'author_email'    => '',
                );
            }
        }

        return $result;
    }
}