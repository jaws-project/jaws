<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Avatar extends Users_Actions_Admin_Default
{
    /**
     * Uploads the avatar
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UploadAvatar()
    {
        $this->gadget->CheckPermission('EditUserPersonal');
        $res = Jaws_Utils::UploadFiles($_FILES,
                                       Jaws_Utils::upload_tmp_dir(),
                                       'gif,jpg,jpeg,png');
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } elseif (empty($res)) {
            $response = array('type'    => 'error',
                              'message' => _t('GLOBAL_ERROR_UPLOAD_4'));
        } else {
            $response = array('type'    => 'notice',
                              'message' => $res['upload_avatar'][0]['host_filename']);
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

    /**
     * Returns avatar as stream data
     *
     * @access  public
     * @return  bool    True on success, false otherwise
     */
    function LoadAvatar()
    {
        $file = jaws()->request->fetch('file', 'get');
        $objImage = Jaws_Image::factory();
        if (!Jaws_Error::IsError($objImage)) {
            if (!empty($file)) {
                $file = preg_replace("/[^[:alnum:]_\.-]*/i", "", $file);
                $result = $objImage->load(Jaws_Utils::upload_tmp_dir(). '/'. $file, true);
                if (!Jaws_Error::IsError($result)) {
                    $result = $objImage->display();
                    if (!Jaws_Error::IsError($result)) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

}