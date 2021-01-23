<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Rotate extends Phoo_Actions_Admin_Default
{
    /**
     * Rotate left a image
     *
     * @access  public
     */
    function RotateLeft()
    {
        $post = $this->gadget->request->fetch(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a class
        //to manage image actions(resize, rotate)
        $model = $this->gadget->model->load('Photos');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image))  {
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=EditPhoto&image='.$post['image'].
            '&album='.$post['fromalbum']);
        }

        include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $this->gadget->session->push($objImage->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(-90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(-90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(-90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $this->gadget->session->push(Jaws::t('IMAGE_ROTATED_LEFT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=EditPhoto&image='.$post['image'].
        '&album='.$post['fromalbum']);
    }

    /**
     * Rotate right a image
     *
     * @access  public
     */
    function RotateRight()
    {
        $post = $this->gadget->request->fetch(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a
        //class to manage image actions(resize, rotate)
        $model = $this->gadget->model->load('Photos');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image)) {
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=EditPhoto&image='.$post['image'].
            '&album='.$post['fromalbum']);
        }

        include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $this->gadget->session->push($objImage->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(ROOT_DATA_PATH. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $this->gadget->session->push(Jaws::t('IMAGE_ROTATED_RIGHT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=EditPhoto&image='.$post['image'].
        '&album='.$post['fromalbum']);
    }
}