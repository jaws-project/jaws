<?php
/**
 * Policy Core Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Captcha extends Jaws_Gadget_Action
{
    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $field
     * @return  array   captcha template assign array
     */
    function xloadCaptcha($field = 'default')
    {
        if (!extension_loaded('gd')) {
            $GLOBALS['log']->Log(JAWS_ERROR, 'LoadCaptcha error: GD extension not loaded');
            return false;
        }

        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                if ($status == 'DISABLED') {
                    return false;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $this->app->session->user->logged)) {
                    return false;
                }
        }

        $assigns = array();

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha);
        $resCaptcha = $objCaptcha->get();
        if (is_array($resCaptcha)) {
            $assigns = array_merge($assigns, $resCaptcha);  // same as $assigns = $resCaptcha
            $assigns['url'] = $this->gadget->urlMap(
                'Captcha',
                array('field' => $field, 'key' => $resCaptcha['key'])
            );
        }

        return $assigns;
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   object  $tpl    Jaws_Template object
     * @param   string  $field
     * @return  bool    True if captcha loaded successfully
     */
    function loadCaptcha(&$tpl, $field = 'default')
    {
        if (!extension_loaded('gd')) {
            $GLOBALS['log']->Log(JAWS_ERROR, 'LoadCaptcha error: GD extension not loaded');
            return false;
        }

        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                if ($status == 'DISABLED') {
                    return false;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $this->app->session->user->logged)) {
                    return false;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha);

        $tpl_base_block = $tpl->GetCurrentBlockPath();
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
                $tpl->SetVariable('entry_title', Jaws::t('CAPTCHA_CASE_INSENSITIVE'));
                $tpl->ParseBlock("$tpl_base_block/image_captcha");
            } else {
                $tpl->SetBlock("$tpl_base_block/text_captcha");
                $tpl->SetVariable('key', $resCaptcha['key']);
                $tpl->SetVariable('lbl_captcha', $resCaptcha['label']);
                $tpl->SetVariable('captcha_title', $resCaptcha['title']);
                $tpl->SetVariable('captcha_text', $resCaptcha['text']);
                $tpl->SetVariable('description', $resCaptcha['description']);
                $tpl->SetVariable('entry_title', Jaws::t('CAPTCHA_CASE_INSENSITIVE'));
                $tpl->ParseBlock("$tpl_base_block/text_captcha");
            }
        }
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $field
     * @param   bool    $cleanup    Delete captcha key after check
     * @return  bool    True if captcha loaded successfully
     */
    function checkCaptcha($field = 'default', $cleanup = true)
    {
        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                if ($status == 'DISABLED') {
                    return true;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $this->app->session->user->logged)) {
                    return true;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha);
        if (true !== $matched = $objCaptcha->check($cleanup)) {
            return Jaws_Error::raiseError(
                Jaws::t('CAPTCHA_ERROR_DOES_NOT_MATCH'),
                is_null($matched)? 404 : 406,
                JAWS_ERROR_NOTICE
            );
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
        $get = $this->gadget->request->fetch(array('field', 'key'), 'get');
        $dCaptcha = $this->gadget->registry->fetch($get['field']. '_captcha_driver');
        $objCaptcha = Jaws_Captcha::getInstance($dCaptcha);
        return $objCaptcha->image($get['key']);
    }
}