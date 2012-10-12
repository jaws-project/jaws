<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Admin_Avatar extends UsersAdminHTML
{
    /**
     * Uploads the avatar
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UploadAvatar()
    {
        $this->CheckPermission('EditAccountProfile');
        $res = Jaws_Utils::UploadFiles($_FILES,
                                       Jaws_Utils::upload_tmp_dir(),
                                       'gif,jpg,jpeg,png');
        if (Jaws_Error::IsError($res)) {
            $response = array('type'    => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type'    => 'notice',
                              'message' => $res['upload_avatar'][0]);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
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
        $request =& Jaws_Request::getInstance();
        $file = $request->get('file', 'get');

        require_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (!Jaws_Error::IsError($objImage)) {
            if (!empty($file)) {
                $file = preg_replace("/[^[:alnum:]_\.-]*/i", "", $file);
                $result = $objImage->load(Jaws_Utils::upload_tmp_dir(). '/'. $file, true);
                if (!Jaws_Error::IsError($result)) {
                    $result = $objImage->display();
                    if (!Jaws_Error::IsError($result)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

}