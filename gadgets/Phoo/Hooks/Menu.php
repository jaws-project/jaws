<?php
/**
 * Phoo - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Phoo
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
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
        $urls[] = array('url'   => $this->gadget->urlMap('Albums'),
                        'title' => $this->gadget->title);

        $urls[] = array('url' => $this->gadget->urlMap('UploadPhotoUI'), 'title' => _t('PHOO_UPLOAD_PHOTO'));

        //Load model
        $model  = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbums();
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $max_size = 20;
            foreach($albums as $a) {
                $url = $this->gadget->urlMap('Photos', array('album' => $a['id']));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($a['name']) > $max_size)?
                                            Jaws_UTF8::substr($a['name'], 0, $max_size).'...' :
                                            $a['name']);
            }
        }

        return $urls;
    }

}