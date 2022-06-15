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
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Moblog extends Jaws_Gadget_Action
{
    /**
     * Get Moblog action params(albums list)
     *
     * @access  public
     * @return  array list of Albums action params(albums list)
     */
    function MoblogLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbums();
        if (!Jaws_Error::IsError($albums)) {
            $palbums = array();
            foreach ($albums as $album) {
                $palbums[$album['id']] = $album['name'];
            }

            $result[] = array(
                'title' => $this::t('ALBUMS'),
                'value' => $palbums
            );
        }

        return $result;
    }

    /**
     * Displays a random image from the gallery listed as a Moblog
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Moblog($aid)
    {
        $tpl = $this->gadget->template->load('Moblog.html');
        $tpl->SetBlock('moblog');
        $tpl->SetVariable('title',$this::t('ACTIONS_MOBLOG'));

        $model = $this->gadget->model->load('Moblog');
        $moblog = $model->GetMoblog($aid);
        if (!Jaws_Error::IsError($moblog)) {
            $objDate = Jaws_Date::getInstance();
            include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($moblog as $mb) {
                $imgData = Jaws_Image::getimagesize(ROOT_DATA_PATH . 'phoo/' . $mb['thumb']);
                if (Jaws_Error::IsError($imgData)) {
                    continue;
                }

                $tpl->SetBlock('moblog/item');
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap(
                        'Photo',
                        array('photo' => $mb['id'], 'album' => $mb['phoo_album_id'])
                    )
                );
                $tpl->SetVariable('name',     $mb['name']);
                $tpl->SetVariable('img_desc', $mb['stripped_description']);
                $tpl->SetVariable('filename', $mb['filename']);
                $tpl->SetVariable('width',    $imgData[0]);
                $tpl->SetVariable('height',   $imgData[1]);
                $tpl->SetVariable('thumb',    $this->app->getDataURL('phoo/' . $mb['thumb']));
                $tpl->SetVariable('medium',   $this->app->getDataURL('phoo/' . $mb['medium']));
                $tpl->SetVariable('image',    $this->app->getDataURL('phoo/' . $mb['image']));
                $tpl->SetVariable('createtime', $objDate->Format($mb['createtime']));
                $tpl->ParseBlock('moblog/item');
            }
        }
        $tpl->ParseBlock('moblog');
        return $tpl->Get();
    }

}