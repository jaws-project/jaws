<?php
/**
 * Directory user's activities hook
 *
 * @category    GadgetHook
 * @package     Directory
 */
class Directory_Hooks_Users extends Jaws_Gadget_Hook
{
    /**
     * Returns User activity in Directory
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   int     $uname  User's name
     * @return  array   An array of user activity
     */
    function Execute($uid, $uname)
    {
        $entity = array();
        $model = $this->gadget->model->load('Files');
        $filesCount = $model->GetFiles(array('user' => $uid, 'published' => true), true);

        if ($filesCount > 0) {
            $entity[0]['title'] = _t('DIRECTORY_FILE_AND_FOLDER');
            $entity[0]['count'] = $filesCount;
            $entity[0]['url'] = $this->gadget->urlMap('Directory', array('user' => $uname));
        }

        return $entity;
    }

}