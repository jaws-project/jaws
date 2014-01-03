<?php
/**
 * Phoo Gadget
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Random extends Jaws_Gadget_Action
{
    /**
     * Get Random action params(albums list)
     *
     * @access  public
     * @return  array list of Albums action params(albums list)
     */
    function RandomLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbums();
        if (!Jaws_Error::IsError($albums)) {
            $palbums = array();
            $palbums[0] = _t('GLOBAL_ALL');
            foreach ($albums as $album) {
                $palbums[$album['id']] = $album['name'];
            }

            $result[] = array(
                'title' => _t('PHOO_ALBUMS'),
                'value' => $palbums
            );
        }

        return $result;
    }

    /**
     * Displays a random image from one of the galleries.
     *
     * @access  public
     * @param   int     $albumid    album ID
     * @return  string   XHTML template content
     * @see Phoo_Model::GetRandomImage()
     */
    function Random($albumid = null)
    {
        $model = $this->gadget->model->load('Random');
        $r = $model->GetRandomImage($albumid);
        if (Jaws_Error::IsError($r) || empty($r)) {
            return false;
        }

        $tpl = $this->gadget->template->load('Random.html');
        $tpl->SetBlock('random_image');
        $imgData = Jaws_Image::getimagesize(JAWS_DATA . 'phoo/' . $r['thumb']);
        if (!Jaws_Error::IsError($imgData)) {
            $tpl->SetVariable('width',  $imgData[0]);
            $tpl->SetVariable('height', $imgData[1]);
        }
        $tpl->SetVariable('title',_t('PHOO_ACTIONS_RANDOM'));
        $tpl->SetVariable(
            'url',
            $this->gadget->urlMap(
                'ViewImage',
                array('id' => $r['id'],'albumid' => $r['phoo_album_id'])
            )
        );
        $tpl->SetVariable('name',     $r['name']);
        $tpl->SetVariable('filename', $r['filename']);
        $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $r['thumb']));
        $tpl->SetVariable('medium',   $GLOBALS['app']->getDataURL('phoo/' . $r['medium']));
        $tpl->SetVariable('image',    $GLOBALS['app']->getDataURL('phoo/' . $r['image']));
        $tpl->SetVariable('img_desc', $r['stripped_description']);
        $tpl->ParseBlock('random_image');
        return $tpl->Get();
    }

}