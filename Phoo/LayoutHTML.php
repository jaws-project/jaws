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
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PhooLayoutHTML
{
    /**
     * Displays a random image from one of the galleries.
     *
     * @access public
     * @param   int     $albumid    album ID
     * @return string   XHTML template content
     * @see PhooModel::GetRandomImage()
     */
    function Random($albumid = null)
    {
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('Random.html');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $r = $model->GetRandomImage($albumid);
        if (!Jaws_Error::IsError($r)) {
            $t->SetBlock('random_image');
            include_once JAWS_PATH . 'include/Jaws/Image.php';
            $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $r['thumb']);
            if (!Jaws_Error::IsError($imgData)) {
                $t->SetVariable('width',  $imgData[0]);
                $t->SetVariable('height', $imgData[1]);
            }
            $t->SetVariable('title',_t('PHOO_RANDOM_IMAGE'));
            $t->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Phoo',
                                                                    'ViewImage',
                                                                    array(
                                                                        'id' => $r['id'],
                                                                        'albumid' => $r['phoo_album_id'])));
            $t->SetVariable('name',     $r['name']);
            $t->SetVariable('filename', $r['filename']);
            $t->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $r['thumb']));
            $t->SetVariable('medium',   $GLOBALS['app']->getDataURL('phoo/' . $r['medium']));
            $t->SetVariable('image',    $GLOBALS['app']->getDataURL('phoo/' . $r['image']));
            $t->SetVariable('img_desc', $r['stripped_description']);
            $t->ParseBlock('random_image');
        }

        return $t->Get();
    }

    /**
     * Displays a random image from the gallery listed as a Moblog
     *
     * @access public
     * @return  string  XHTML template content
     */
    function Moblog()
    {
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('Moblog.html');
        $t->SetBlock('moblog');
        $t->SetVariable('title',_t('PHOO_MOBLOG'));

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

                $t->SetBlock('moblog/item');
                $t->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Phoo',
                                                                       'ViewImage',
                                                                       array('id' => $mb['id'],
                                                                             'albumid' => $mb['phoo_album_id'])));
                $t->SetVariable('name',     $mb['name']);
                $t->SetVariable('img_desc', $mb['stripped_description']);
                $t->SetVariable('filename', $mb['filename']);
                $t->SetVariable('width',    $imgData[0]);
                $t->SetVariable('height',   $imgData[1]);
                $t->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $mb['thumb']));
                $t->SetVariable('createtime', $objDate->Format($mb['createtime']));
                $t->ParseBlock('moblog/item');
            }
        }
        $t->ParseBlock('moblog');
        return $t->Get();
    }

    /**
     * Displays a list of recent phoo comments ordered by date
     *
     * @access public
     * @return  string  XHTML template content
     */
    function RecentComments()
    {
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('RecentComments.html');
        $tpl->SetBlock('recent_comments');
        $tpl->SetVariable('title', _t('PHOO_RECENT_COMMENTS'));
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $comments = $model->GetRecentComments();
        if (!Jaws_Error::IsError($comments)) {
            foreach ($comments as $c) {
                $tpl->SetBlock('recent_comments/item');
                $albumid = $model->GetImageALbum($c['gadget_reference']);
                $url = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewImage',
                                            array('id' => $c['gadget_reference'], 'albumid' => $albumid));
                $tpl->SetVariable('on', _t('GLOBAL_ON'));
                $tpl->SetVariablesArray($c);
                $tpl->SetVariable('url', $url . '#comment'.$c['id']);
                $tpl->ParseBlock('recent_comments/item');
            }
        }
        $tpl->ParseBlock('recent_comments');

        return $tpl->Get();
    }

    /**
     * Displays an index of galleries.
     *
     * @access public
     * @return  string XHTML template content
     */
    function AlbumList()
    {
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('Albums.html');
        $t->SetBlock('albums');
        $t->SetVariable('title', _t('PHOO_ALBUMS'));
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $albums = $model->GetAlbumList();
        if (!Jaws_Error::IsError($albums)) {
            $date = $GLOBALS['app']->loadDate();
            require_once JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($albums as $album) {
                if (!isset($album['qty'])) {
                    continue;
                }

                $t->SetBlock('albums/item');
                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $album['thumb']);
                if (!Jaws_Error::IsError($imgData)) {
                    $t->SetVariable('width',    $imgData[0]);
                    $t->SetVariable('height',   $imgData[1]);
                }
                $url = $GLOBALS['app']->Map->GetURLFor('Phoo','ViewAlbum', array('id' => $album['id']));
                $t->SetVariable('url',      $url);
                $t->SetVariable('name',     $album['name']);
                $t->SetVariable('filename', $album['filename']);
                $t->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $album['thumb']));
                $t->SetVariable('howmany',  _t('PHOO_NUM_PHOTOS_ALBUM', $album['qty']));
                $t->SetVariable('description', Jaws_Gadget::ParseText($album['description'], 'Phoo'));
                $t->SetVariable('createtime', $date->Format($album['createtime']));
                $t->ParseBlock('albums/item');
            }
        }
        $t->ParseBlock('albums');
        return $t->Get();
    }
}