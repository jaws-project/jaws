<?php
/**
 * Comments Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2008-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Admin_Settings extends Comments_Actions_Admin_Default
{
    /**
     * Builds admin properties UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Settings()
    {
        $this->gadget->CheckPermission('Settings');
        $this->AjaxMe('script.js');

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Settings') : $menubar;
        $assigns['allow_comments'] = $this->gadget->registry->fetch('allow_comments');
        $assigns['default_comment_status'] = $this->gadget->registry->fetch('default_comment_status');
        $assigns['order_type'] = $this->gadget->registry->fetch('order_type');

        $assigns['allowCommentsItems'] = array(
            'true' => Jaws::t('YES'),
            'restricted' => _t('COMMENTS_ALLOW_COMMENTS_RESTRICTED'),
            'false' => Jaws::t('NO')
        );
        $assigns['defaultStatusItems'] = array(
            1 => _t('COMMENTS_STATUS_APPROVED'),
            2 => _t('COMMENTS_STATUS_WAITING')
        );
        $assigns['orderTypeItems'] = array(
            1 => Jaws::t('CREATETIME'). ' &uarr;',
            2 => Jaws::t('CREATETIME'). ' &darr;'
        );

        return $this->gadget->template->xLoadAdmin('Settings.html')->render($assigns);
    }

    /**
     * Update Settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveSettings()
    {
        $this->gadget->CheckPermission('Settings');
        $post = $this->gadget->request->fetch(array('allow_comments', 'default_comment_status', 'order_type'), 'post');
        $res = $this->gadget->model->loadAdmin('Settings')->SaveSettings(
            $post['allow_comments'],
            $post['default_comment_status'],
            $post['order_type']
        );
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response($res->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('COMMENTS_PROPERTIES_UPDATED'),
            RESPONSE_NOTICE
        );
    }
}