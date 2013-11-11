<?php
/**
 * Phoo - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Phoo
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Phoo', 'AlbumList'),
                        'title' => _t('PHOO_NAME'));

        //Load model
        $model  = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbums();
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $max_size = 20;
            foreach($albums as $a) {
                $url = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewAlbum', array('id' => $a['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($a['name']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($a['name'], 0, $max_size).'...' :
                                            $a['name']);
            }
        }

        //Load model
        $model  = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::IsError($groups) && !empty($groups)) {
            $max_size = 20;
            foreach($groups as $group) {
                $url = $GLOBALS['app']->Map->GetURLFor('Phoo', 'AlbumList', array('group' => $group['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($group['name']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($group['name'], 0, $max_size).'...' :
                                            $group['name']);
            }
        }


        return $urls;
    }

}