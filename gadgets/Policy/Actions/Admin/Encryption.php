<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Actions_Admin_Encryption extends Policy_Actions_Admin_Default
{
    /**
     * Encryption action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function Encryption()
    {
        $this->gadget->CheckPermission('Encryption');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Encryption.html');
        $tpl->SetBlock('encryption');

        // Sidebar
        $tpl->SetVariable('sidebar', $this->SideBar('Encryption'));
        $tpl->SetVariable('legend_title', _t('POLICY_ENCRYPTION'));

        $useEncryption =& Piwi::CreateWidget('Combo', 'enabled');
        $useEncryption->setID('enabled');
        $useEncryption->AddOption(_t('GLOBAL_NO'),  'false');
        $useEncryption->AddOption(_t('GLOBAL_YES'), 'true');
        $useEncryption->SetDefault($this->gadget->registry->fetch('crypt_enabled'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('enabled', $useEncryption->Get());

        $keyAge =& Piwi::CreateWidget('Combo', 'key_age');
        $keyAge->setID('key_age');
        $keyAge->AddOption(_t('GLOBAL_DATE_MINUTES', 10),   600);
        $keyAge->AddOption(_t('GLOBAL_DATE_HOURS',   1),   3600);
        $keyAge->AddOption(_t('GLOBAL_DATE_HOURS',   5),  18000);
        $keyAge->AddOption(_t('GLOBAL_DATE_DAYS',    1),  86400);
        $keyAge->AddOption(_t('GLOBAL_DATE_WEEKS',   1), 604800);
        $keyAge->SetDefault($this->gadget->registry->fetch('crypt_key_age'));
        $keyAge->SetEnabled($this->gadget->GetPermission('ManageEncryptionKey'));
        $tpl->SetVariable('lbl_key_age', _t('POLICY_ENCRYPTION_KEY_AGE'));
        $tpl->SetVariable('key_age', $keyAge->Get());

        $keyLen =& Piwi::CreateWidget('Combo', 'key_len');
        $keyLen->setID('key_len');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_512BIT'),  '512');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_1024BIT'), '1024');
        $keyLen->AddOption(_t('POLICY_ENCRYPTION_2048BIT'), '2048');
        $keyLen->SetDefault($this->gadget->registry->fetch('crypt_key_len'));
        $keyLen->SetEnabled($this->gadget->GetPermission('ManageEncryptionKey'));
        $tpl->SetVariable('lbl_key_len', _t('POLICY_ENCRYPTION_KEY_LEN'));
        $tpl->SetVariable('key_len', $keyLen->Get());

        $date = Jaws_Date::getInstance();
        $keyStartDate =& Piwi::CreateWidget('Entry', 'key_start_date',
            $date->Format((int)$this->gadget->registry->fetch('crypt_key_start_date')));
        $keyStartDate->setID('key_start_date');
        $keyStartDate->setSize(30);
        $keyStartDate->SetEnabled(false);
        $tpl->SetVariable('lbl_key_start_date', _t('POLICY_ENCRYPTION_KEY_START_DATE'));
        $tpl->SetVariable('key_start_date', $keyStartDate->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, 'javascript:saveEncryptionSettings();');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $tpl->ParseBlock('encryption');
        return $tpl->Get();
    }
}