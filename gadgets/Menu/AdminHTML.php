<?php
/**
 * Menu Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Menu
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Menu_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays gadget administration section
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        $gadget = $GLOBALS['app']->LoadGadget('Menu', 'AdminHTML', 'Menu');
        return $gadget->Menu();
    }

    /**
     * Uploads the image file
     *
     * @access  public
     * @return  string  javascript script snippet
     */
    function UploadImage()
    {
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), 'gif,jpg,jpeg,png,bmp,ico');
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type'    => 'notice',
                              'message' => $res['upload_image'][0]['host_filename']);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

    /**
     * Returns menu image as stream data
     *
     * @access  public
     * @return  bool    True on successful, False otherwise
     */
    function LoadImage()
    {
        $request =& Jaws_Request::getInstance();
        $params = $request->get(array('id', 'file'), 'get');

        require_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (!Jaws_Error::IsError($objImage)) {
            if (is_null($params['file'])) {
                $model = $GLOBALS['app']->LoadGadget('Menu', 'Model', 'Menu');
                $result = $model->GetMenuImage($params['id']);
                if (!Jaws_Error::IsError($result)) {
                    $result = $objImage->setData($result, true);
                }
            } else {
                $params['file'] = preg_replace("/[^[:alnum:]_\.-]*/i", "", $params['file']);
                $result = $objImage->load(Jaws_Utils::upload_tmp_dir(). '/'. $params['file'], true);
            }

            if (!Jaws_Error::IsError($result)) {
                $result = $objImage->display();
                if (!Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        }

        return false;
    }
}