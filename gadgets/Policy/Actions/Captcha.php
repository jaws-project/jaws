<?php
/**
 * Policy Core Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Captcha extends Jaws_Gadget_Action
{
    /**
     * Load and get captcha
     *
     * @access  public
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @param   string  $field
     * @return  bool    True if captcha loaded successfully
     */
    function loadCaptcha(&$tpl, $tpl_base_block, $field = 'default')
    {
        if (!extension_loaded('gd')) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'LoadCaptcha error: GD extension not loaded');
            return false;
        }

        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                $bad_logins = (int)$GLOBALS['app']->Session->GetAttribute('bad_login_count');
                if (($status == 'DISABLED') || ($bad_logins < (int)$status)) {
                    return false;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
                    return false;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha, $field);

        $resCaptcha = $objCaptcha->get();
        if (empty($resCaptcha['key'])) {
            $tpl->SetBlock("$tpl_base_block/block_captcha");
            $tpl->SetVariable('lbl_captcha', $resCaptcha['label']);
            $tpl->SetVariable('captcha_title', $resCaptcha['title']);
            $tpl->SetVariable('captcha_text', $resCaptcha['text']);
            $tpl->ParseBlock("$tpl_base_block/block_captcha");
        } else {
            if (empty($resCaptcha['text'])) {
                $tpl->SetBlock("$tpl_base_block/image_captcha");
                $tpl->SetVariable('key', $resCaptcha['key']);
                $tpl->SetVariable('lbl_captcha', $resCaptcha['label']);
                $tpl->SetVariable('captcha_title', $resCaptcha['title']);
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap('Captcha', array('field' => $field, 'key' => $resCaptcha['key']))
                );
                $tpl->SetVariable('description', $resCaptcha['description']);
                $tpl->SetVariable('entry_title', _t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
                $tpl->ParseBlock("$tpl_base_block/image_captcha");
            } else {
                $tpl->SetBlock("$tpl_base_block/text_captcha");
                $tpl->SetVariable('key', $resCaptcha['key']);
                $tpl->SetVariable('lbl_captcha', $resCaptcha['label']);
                $tpl->SetVariable('captcha_title', $resCaptcha['title']);
                $tpl->SetVariable('captcha_text', $resCaptcha['text']);
                $tpl->SetVariable('description', $resCaptcha['description']);
                $tpl->SetVariable('entry_title', _t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
                $tpl->ParseBlock("$tpl_base_block/text_captcha");
            }
        }
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $field
     * @return  bool    True if captcha loaded successfully
     */
    function checkCaptcha($field = 'default')
    {
        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                $bad_logins = (int)$GLOBALS['app']->Session->GetAttribute('bad_login_count');
                if (($status == 'DISABLED') || ($bad_logins < (int)$status)) {
                    return true;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
                    return true;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha, $field);
        if (!$objCaptcha->check()) {
            return Jaws_Error::raiseError(_t('GLOBAL_CAPTCHA_ERROR_DOES_NOT_MATCH'),
                'Jaws_Captcha',
                JAWS_ERROR_NOTICE);
        }

        return true;
    }

    /**
     * Tricky way to get the captcha image...
     * @access  public
     * @return PNG image
     */
    function Captcha()
    {
        $get = jaws()->request->fetch(array('field', 'key'), 'get');

        $dCaptcha = $this->gadget->registry->fetch($get['field']. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha, $get['field']);
        $objCaptcha->image($get['key']);
    }
}