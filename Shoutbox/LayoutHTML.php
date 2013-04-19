<?php
/**
 * Shoutbox Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Shoutbox
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays the shoutbox
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Shoutbox/templates/');
        $tpl->Load('Shoutbox.html');
        $tpl->SetBlock('shoutbox');
        $tpl->SetVariable('title', _t('SHOUTBOX_NAME'));

        if ($GLOBALS['app']->Session->Logged() ||
            $this->gadget->registry->get('anon_post_authority') == 'true')
        {
            $tpl->SetBlock('shoutbox/fieldset');
            $tpl->SetVariable('base_script', BASE_SCRIPT);
            $tpl->SetVariable('message', _t('SHOUTBOX_MESSAGE'));
            $tpl->SetVariable('send', _t('SHOUTBOX_SEND'));

            $name  = $GLOBALS['app']->Session->GetCookie('visitor_name');
            $email = $GLOBALS['app']->Session->GetCookie('visitor_email');
            $url   = $GLOBALS['app']->Session->GetCookie('visitor_url');

            $rand = rand();
            $tpl->SetVariable('rand', $rand);
            if (!$GLOBALS['app']->Session->Logged()) {
                $tpl->SetBlock('shoutbox/fieldset/info-box');
                $url_value = empty($url)? 'http://' : Jaws_XSS::filter($url);
                $tpl->SetVariable('url', _t('GLOBAL_URL'));
                $tpl->SetVariable('urlvalue', $url_value);
                $tpl->SetVariable('rand', $rand);
                $tpl->SetVariable('name', _t('GLOBAL_NAME'));
                $tpl->SetVariable('namevalue', isset($name) ? Jaws_XSS::filter($name) : '');
                $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('emailvalue', isset($email) ? Jaws_XSS::filter($email) : '');
                $tpl->ParseBlock('shoutbox/fieldset/info-box');
            }

            $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
            if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
                $tpl->SetBlock('shoutbox/fieldset/captcha');
                $tpl->SetVariable('lbl_captcha', $label);
                $tpl->SetVariable('captcha', $captcha);
                if (!empty($entry)) {
                    $tpl->SetVariable('captchavalue', $entry);
                }
                $tpl->SetVariable('captcha_msg', $description);
                $tpl->ParseBlock('shoutbox/fieldset/captcha');
            }

            $tpl->ParseBlock('shoutbox/fieldset');
        } else {
            $tpl->SetBlock('shoutbox/unregistered');
            $tpl->SetVariable('msg', _t('GLOBAL_ERROR_ACCESS_RESTRICTED',
                                        $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                                        $GLOBALS['app']->Map->GetURLFor('Users', 'Registration')));
            $tpl->ParseBlock('shoutbox/unregistered');
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Shoutbox')) {
            $tpl->SetBlock('shoutbox/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('shoutbox/response');
        }

        $this->AjaxMe('site_script.js');
        $tpl->SetVariable('shoutbox_messages', $this->GetMessages());
        $tpl->ParseBlock('shoutbox');
        return $tpl->Get();
    }

    /**
     * Get the shoutbox messages list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetMessages()
    {
        $model = $GLOBALS['app']->LoadGadget('Shoutbox', 'Model');
        $entries = $model->GetEntries($this->gadget->registry->get('limit'));
        if (!Jaws_Error::IsError($entries) && !empty($entries)) {
            $tpl = new Jaws_Template('gadgets/Shoutbox/templates/');
            $tpl->Load('Shoutbox.html');
            $tpl->SetBlock('messages');

            $date = $GLOBALS['app']->loadDate();
            foreach ($entries as $entry) {
                $tpl->SetBlock('messages/entry');
                $tpl->SetVariable('name', Jaws_XSS::filter($entry['name']));
                $tpl->SetVariable('email', Jaws_XSS::filter($entry['email']));
                $tpl->SetVariable('url', Jaws_XSS::filter($entry['url']));
                $tpl->SetVariable('updatetime', $date->Format($entry['createtime']));
                $tpl->SetVariable('message', Jaws_String::AutoParagraph($entry['msg_txt']));
                if ($entry['status'] == 3) {
                   $tpl->SetVariable('status_message', _t('SHOUTBOX_COMMENT_IS_SPAM'));
                } elseif ($entry['status'] == 2) {
                    $tpl->SetVariable('status_message', _t('SHOUTBOX_COMMENT_IS_WAITING'));
                } else {
                    $tpl->SetVariable('status_message', '&nbsp;');
                }
                $tpl->ParseBlock('messages/entry');
            }
            $tpl->ParseBlock('messages');
            return $tpl->Get();
        }

        return '';
    }

}