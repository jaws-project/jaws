<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Send extends Jaws_Gadget_Action
{
    /**
     * Save contact in database
     *
     * @access  public
     */
    function Send()
    {
        $post = $this->gadget->request->fetch(
            array(
                'name', 'email', 'company', 'url', 'tel', 'fax',
                'mobile', 'address', 'recipient', 'subject', 'message'
            ),
            'post'
        );

        if ($this->app->session->logged()) {
            $post['name']   = $this->app->session->getAttribute('nickname');
            $post['email']  = $this->app->session->getAttribute('email');
            $post['mobile'] = $this->app->session->getAttribute('mobile');
            $post['url']    = $this->app->session->getAttribute('url');
        }

        if (trim($post['name'])    == '' ||
            trim($post['subject']) == '' ||
            trim($post['message']) == '')
        {
            $this->gadget->session->push(
                _t('CONTACT_INCOMPLETE_FIELDS'),
                'Contact',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $this->gadget->session->push(
                $resCheck->getMessage(),
                'Contact',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
        }

        // email
        $post['email'] = trim($post['email']);
        if (!empty($post['email'])) {
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $post['email'])) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_EMAIL_ADDRESS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $post['email'] = strtolower($post['email']);
            $blockedDomains = $this->app->registry->fetch('blocked_domains', 'Policy');
            if (false !== strpos($blockedDomains, "\n".substr(strrchr($post['email'], '@'), 1))) {
                return Jaws_Error::raiseError(
                    _t('GLOBAL_ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($post['email'], '@'), 1)),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        // mobile
        $post['mobile'] = isset($post['mobile'])? trim($post['mobile']) : '';
        if (!empty($post['mobile'])) {
            if (!empty($post['mobile'])) {
                if (!preg_match("/^[00|\+|0]\d{10,16}$/", $post['mobile'])) {
                    return Jaws_Error::raiseError(
                        _t('GLOBAL_ERROR_INVALID_MOBILE_NUMBER'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        if (empty($post['email']) && empty($post['mobile'])) {
            $this->gadget->session->push(
                _t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'),
                'Contact',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
        }
/*
        if ($this->gadget->registry->fetch('use_antispam') == 'true') {
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $post['email'])) {
                $this->gadget->session->push(
                    _t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'),
                    'Contact',
                    RESPONSE_ERROR,
                    $post
                );
                Jaws_Header::Referrer();
            }
        }
*/
        $attachment = null;
        if (($this->gadget->registry->fetch('enable_attachment') == 'true') &&
            $this->gadget->GetPermission('AllowAttachment')) 
        {
            $attach = Jaws_Utils::UploadFiles($_FILES,
                                              JAWS_DATA. 'contact',
                                              '',
                                              false);
            if (Jaws_Error::IsError($attach)) {
                $this->gadget->session->push(
                    $attach->getMessage(),
                    'Contact',
                    RESPONSE_ERROR,
                    $post
                );
                Jaws_Header::Referrer();
            }

            if (!empty($attach)) {
                $attachment = $attach['attachment'][0]['host_filename'];
            }
        }

        try {
            $result =  $this->gadget->model->load('Contacts')->InsertContact(
                $post['name'],
                $post['email'],
                $post['company'],
                $post['url'],
                $post['tel'],
                $post['fax'],
                $post['mobile'],
                $post['address'],
                $post['recipient'],
                $post['subject'],
                $attachment,
                $post['message']
            );
            if (Jaws_Error::IsError($result)) {
                throw new Exception(_t('CONTACT_RESULT_ERROR_DB'), 500);
            }

            if (!empty($post['recipient'])) {
                $recipient = $this->gadget->model->load('Recipients')->GetRecipient((int)$post['recipient']);
                if (Jaws_Error::IsError($recipient)) {
                    throw new Exception(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'), 500);
                }
            }

            if (empty($recipient)) {
                $recipient = array(
                    'inform_type' => 'Email',
                    //
                );
            }

            if (!empty($recipient['inform_type'])) {
                $classname = 'Contact_Informs_'. $recipient['inform_type'];
                $objSender = new $classname($this->gadget);
                $objSender->SendToRecipient($recipient, $post);
                // FIXME:: check result
            }

            $this->gadget->session->push(
                _t('CONTACT_RESULT_SENT'),
                'Contact',
                RESPONSE_NOTICE
            );

        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                'Contact',
                RESPONSE_ERROR,
                $post
            );
        }

        return Jaws_Header::Referrer();
    }

}