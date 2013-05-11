<?php
/**
 * Phoo Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_LayoutHTML extends Jaws_Gadget_HTML
{
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
        $tpl = $this->gadget->loadTemplate('Random.html');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $r = $model->GetRandomImage($albumid);
        if (!Jaws_Error::IsError($r)) {
            $tpl->SetBlock('random_image');
            include_once JAWS_PATH . 'include/Jaws/Image.php';
            $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $r['thumb']);
            if (!Jaws_Error::IsError($imgData)) {
                $tpl->SetVariable('width',  $imgData[0]);
                $tpl->SetVariable('height', $imgData[1]);
            }
            $tpl->SetVariable('title',_t('PHOO_ACTIONS_RANDOM'));
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Phoo',
                                                                    'ViewImage',
                                                                    array(
                                                                        'id' => $r['id'],
                                                                        'albumid' => $r['phoo_album_id'])));
            $tpl->SetVariable('name',     $r['name']);
            $tpl->SetVariable('filename', $r['filename']);
            $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $r['thumb']));
            $tpl->SetVariable('medium',   $GLOBALS['app']->getDataURL('phoo/' . $r['medium']));
            $tpl->SetVariable('image',    $GLOBALS['app']->getDataURL('phoo/' . $r['image']));
            $tpl->SetVariable('img_desc', $r['stripped_description']);
            $tpl->ParseBlock('random_image');
        }

        return $tpl->Get();
    }

    /**
     * Displays a random image from the gallery listed as a Moblog
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Moblog()
    {
        $tpl = $this->gadget->loadTemplate('Moblog.html');
        $tpl->SetBlock('moblog');
        $tpl->SetVariable('title',_t('PHOO_ACTIONS_MOBLOG'));

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $moblog = $model->GetMoblog();
        if (!Jaws_Error::IsError($moblog)) {
            $objDate = $GLOBALS['app']->loadDate();
            include_once JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($moblog as $mb) {
                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $mb['thumb']);
                if (Jaws_Error::IsError($imgData)) {
                    continue;
                }

                $tpl->SetBlock('moblog/item');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Phoo',
                                                                       'ViewImage',
                                                                       array('id' => $mb['id'],
                                                                             'albumid' => $mb['phoo_album_id'])));
                $tpl->SetVariable('name',     $mb['name']);
                $tpl->SetVariable('img_desc', $mb['stripped_description']);
                $tpl->SetVariable('filename', $mb['filename']);
                $tpl->SetVariable('width',    $imgData[0]);
                $tpl->SetVariable('height',   $imgData[1]);
                $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $mb['thumb']));
                $tpl->SetVariable('createtime', $objDate->Format($mb['createtime']));
                $tpl->ParseBlock('moblog/item');
            }
        }
        $tpl->ParseBlock('moblog');
        return $tpl->Get();
    }

    /**
     * Displays an index of galleries.
     *
     * @access  public
     * @return  string XHTML template content
     */
    function AlbumList()
    {
        $tpl = $this->gadget->loadTemplate('Albums.html');
        $tpl->SetBlock('albums');
        $tpl->SetVariable('title', _t('PHOO_ALBUMS'));
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $albums = $model->GetAlbumList();
        if (!Jaws_Error::IsError($albums)) {
            $date = $GLOBALS['app']->loadDate();
            require_once JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($albums as $album) {
                if (!isset($album['qty'])) {
                    continue;
                }

                $tpl->SetBlock('albums/item');
                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $album['thumb']);
                if (!Jaws_Error::IsError($imgData)) {
                    $tpl->SetVariable('width',    $imgData[0]);
                    $tpl->SetVariable('height',   $imgData[1]);
                }
                $url = $GLOBALS['app']->Map->GetURLFor('Phoo','ViewAlbum', array('id' => $album['id']));
                $tpl->SetVariable('url',      $url);
                $tpl->SetVariable('name',     $album['name']);
                $tpl->SetVariable('filename', $album['filename']);
                $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $album['thumb']));
                $tpl->SetVariable('howmany',  _t('PHOO_NUM_PHOTOS_ALBUM', $album['qty']));
                $tpl->SetVariable('description', $this->gadget->ParseText($album['description']));
                $tpl->SetVariable('createtime', $date->Format($album['createtime']));
                $tpl->ParseBlock('albums/item');
            }
        }
        $tpl->ParseBlock('albums');
        return $tpl->Get();
    }

}