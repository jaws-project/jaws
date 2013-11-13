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
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Albums extends Jaws_Gadget_Action
{
    /**
     * Get AlbumList action params(group list)
     *
     * @access  public
     * @return  array list of AlbumList action params(group list)
     */
    function AlbumListLayoutParams()
    {
        $result = array();
        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::IsError($groups)) {
            $pgroups = array();
            $pgroups[0] = _t('GLOBAL_ALL');
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['name'];
            }

            $result[] = array(
                'title' => _t('GLOBAL_GROUP'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Displays an index of galleries.
     *
     * @access  public
     * @param   int      $gid         Group ID
     * @return  string XHTML template content
     */
    function AlbumList($gid = null)
    {
        $tpl = $this->gadget->template->load('Albums.html');
        $tpl->SetBlock('albums');
        $tpl->SetVariable('title', _t('PHOO_ALBUMS'));
        $model = $this->gadget->model->load('Albums');

        if (empty($gid)) {
            $gid = jaws()->request->fetch('group', 'get');
        }
        if (is_null($gid)) {
            $group = (int)$this->gadget->request->fetch('group');
            if (!empty($group) && $group != 0) {
                $gid = $group;
            }
        }

        $albums = $model->GetAlbumList($gid);
        if (!Jaws_Error::IsError($albums)) {
            $date = Jaws_Date::getInstance();
            $agModel = $this->gadget->model->load('AlbumGroup');
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

                $pos = 1;
                $groups = $agModel->GetAlbumGroupsInfo($album['id']);
                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        $url = $GLOBALS['app']->Map->GetURLFor('Phoo', 'AlbumList', array('group' => $group['fast_url']));
                        $tpl->SetBlock('albums/item/group');
                        $tpl->SetVariable('url', $url);
                        $tpl->SetVariable('name', $group['name']);
                        if ($pos == count($groups)) {
                            $tpl->SetVariable('separator', '');
                        } else {
                            $tpl->SetVariable('separator', ',');
                        }
                        $pos++;
                        $tpl->ParseBlock('albums/item/group');
                    }
                }
                $tpl->ParseBlock('albums/item');
            }
        }
        $tpl->ParseBlock('albums');
        return $tpl->Get();
    }

}