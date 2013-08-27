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
class Phoo_Actions_Random extends Jaws_Gadget_HTML
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
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model', 'Random');
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

}