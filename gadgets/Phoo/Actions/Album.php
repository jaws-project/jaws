<?php
/**
 * Phoo Gadget
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Album extends Jaws_Gadget_Action
{
    /**
     * Get Album action params(albums list)
     *
     * @access  public
     * @return  array list of Albums action params(albums users list)
     */
    function AlbumLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbumList();
        if (!Jaws_Error::IsError($albums)) {
            $palbums = array();
            foreach ($albums as $album) {
                if (!isset($album['qty'])) {
                    continue;
                }
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
     * Displays an index of gallerie.
     *
     * @access  public
     * @return  string XHTML template content
     */
    function Album()
    {
        $tpl = $this->gadget->template->load('Albums.html');
        $tpl->SetBlock('albums');
        $tpl->SetVariable('title', _t('PHOO_ALBUMS'));
        $model = $this->gadget->model->load('Albums');
        $albums = $model->GetAlbumList();
        if (!Jaws_Error::IsError($albums)) {
            $date = Jaws_Date::getInstance();
            foreach ($albums as $album) {
                if (!isset($album['qty'])) {
                    continue;
                }

                $tpl->SetBlock('albums/item');
                $imgData = Jaws_Image::getimagesize(JAWS_DATA . 'phoo/' . $album['thumb']);
                if (!Jaws_Error::IsError($imgData)) {
                    $tpl->SetVariable('width',    $imgData[0]);
                    $tpl->SetVariable('height',   $imgData[1]);
                }
                $url = $this->gadget->urlMap('Photos', array('album' => $album['id']));
                $tpl->SetVariable('url',      $url);
                $tpl->SetVariable('name',     $album['name']);
                $tpl->SetVariable('filename', $album['filename']);
                $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $album['thumb']));
                $tpl->SetVariable('howmany',  _t('PHOO_NUM_PHOTOS_ALBUM', $album['qty']));
                $tpl->SetVariable('description', $this->gadget->plugin->parseAdmin($album['description']));
                $tpl->SetVariable('createtime', $date->Format($album['createtime']));
                $tpl->ParseBlock('albums/item');
            }
        }
        $tpl->ParseBlock('albums');
        return $tpl->Get();
    }

}