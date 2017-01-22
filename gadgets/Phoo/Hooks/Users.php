<?php
/**
 * Phoo user's activities hook
 *
 * @category    GadgetHook
 * @package     Phoo
 */
class Phoo_Hooks_Users extends Jaws_Gadget_Hook
{
    /**
     * Returns user's activities
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   string  $uname  User's name
     * @return  array   An array of user's activity
     */
    function Execute($uid, $uname)
    {
        $entities = array();

        // user photos
        $entities[0]['title'] = _t('PHOO_USER_PHOTOS', $uname);
        $entities[0]['url'] = $this->gadget->urlMap('ViewUserPhotos', array('user' => $uid));

        return $entities;
    }

}