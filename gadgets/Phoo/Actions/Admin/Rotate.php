<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2014 Jaws Development Group
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
        $post = jaws()->request->fetch(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a class
        //to manage image actions(resize, rotate)
        $model = $this->gadget->model->load('Photos');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image))  {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
            '&album='.$post['fromalbum']);
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $GLOBALS['app']->Session->PushLastResponse($objImage->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(-90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(-90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(-90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_IMAGE_ROTATED_LEFT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
        '&album='.$post['fromalbum']);
    }

    /**
     * Rotate right a image
     *
     * @access  public
     */
    function RotateRight()
    {
        $post = jaws()->request->fetch(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a
        //class to manage image actions(resize, rotate)
        $model = $this->gadget->model->load('Photos');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
            '&album='.$post['fromalbum']);
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_IMAGE_ROTATED_RIGHT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
        '&album='.$post['fromalbum']);
    }
}