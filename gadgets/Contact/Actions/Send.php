<?php
/**
 * Contact Gadget
 *
 * @category   Gadget
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2020 Jaws Development Group
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

        if ($this->app->session->user->logged) {
            $post['name']   = $this->app->session->user->nickname;
            $post['email']  = $this->app->session->user->email;
            $post['mobile'] = $this->app->session->user->mobile;
            $post['url']    = $this->app->session->user->url;
        }

        if (trim($post['name'])    == '' ||
            trim($post['subject']) == '' ||
            trim($post['message']) == '')
        {
            $this->gadget->session->push(
                _t('CONTACT_INCOMPLETE_FIELDS'),
                RESPONSE_ERROR,
                'Contact',
                $post
            );
            Jaws_Header::Referrer();
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $this->gadget->session->push(
                $resCheck->getMessage(),
                RESPONSE_ERROR,
                'Contact',
                $post
            );
            Jaws_Header::Referrer();
        }

        // email
        $post['email'] = trim($post['email']);
        if (!empty($post['email'])) {
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $post['email'])) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_ADDRESS'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
            $post['email'] = strtolower($post['email']);
            $blockedDomains = $this->app->registry->fetch('blocked_domains', 'Policy');
            if (false !== strpos($blockedDomains, "\n".substr(strrchr($post['email'], '@'), 1))) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_INVALID_EMAIL_DOMAIN', substr(strrchr($post['email'], '@'), 1)),
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
                        Jaws::t('ERROR_INVALID_MOBILE_NUMBER'),
                        __FUNCTION__,
                        JAWS_ERROR_NOTICE
                    );
                }
            }
        }

        if (empty($post['email']) && empty($post['mobile'])) {
            $this->gadget->session->push(
                _t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'),
                RESPONSE_ERROR,
                'Contact',
                $post
            );
            Jaws_Header::Referrer();
        }
/*
        if ($this->gadget->registry->fetch('use_antispam') == 'true') {
            if (!preg_match("/^[[:alnum:]\-_.]+\@[[:alnum:]\-_.]+\.[[:alnum:]\-_]+$/", $post['email'])) {
                $this->gadget->session->push(
                    _t('CONTACT_RESULT_BAD_EMAIL_ADDRESS'),
                    RESPONSE_ERROR,
                    'Contact',
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
            $attach = $this->app->fileManagement::uploadFiles($_FILES,
                                              ROOT_DATA_PATH. 'contact',
                                              '',
                                              false);
            if (Jaws_Error::IsError($attach)) {
                $this->gadget->session->push(
                    $attach->getMessage(),
                    RESPONSE_ERROR,
                    'Contact',
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
                RESPONSE_NOTICE,
                'Contact'
            );

        } catch (Exception $error) {
            $this->gadget->session->push(
                $error->getMessage(),
                RESPONSE_ERROR,
                'Contact',
                $post
            );
        }

        return Jaws_Header::Referrer();
    }

}