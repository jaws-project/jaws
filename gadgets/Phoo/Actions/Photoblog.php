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
class Phoo_Actions_Photoblog extends Jaws_Gadget_Action
{
    /**
     * I'm not sure what this does... gets the authors photo maybe?
     *
     * @access  public
     * @see Phoo_Model::GetAsPortrait()
     * @return  string   XHTML template content
     * @todo Better docblock
     */
    function PhotoblogPortrait()
    {
        $photoid = jaws()->request->fetch('photoid', 'get');
        $model = $this->gadget->model->load('Photoblog');
        $entries = $model->GetAsPortrait($photoid);
        if (Jaws_Error::IsError($entries)) {
            return '';
        }

        if (count($entries) <= 0) {
            return '';
        }

        $this->SetTitle(_t('PHOO_PHOTOBLOG'));
        $tpl = $this->gadget->template->load('Photoblog.html');
        $tpl->SetBlock('photoblog_portrait');
        $first = true;
        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $date = $GLOBALS['app']->loadDate();
        foreach ($entries as $entry) {
            if (empty($photoid)) {
                if (!$first) {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['thumb']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/item');
                    $tpl->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->gadget->urlMap('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $tpl->SetVariable('url', $url);
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/item');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/main');
                    $tpl->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $tpl->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/main');
                }
                $first = false;
            } else {
                if ($photoid == $entry['id']) {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/main');
                    $tpl->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $tpl->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/main');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['thumb']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/item');
                    $tpl->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->gadget->urlMap('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $tpl->SetVariable('url', $url);
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/item');
                }
            }
        }
        $tpl->ParseBlock('photoblog_portrait');
        return $tpl->Get();
    }

}