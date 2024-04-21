<?php
/**
 * Contact Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Prepares the contacs menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Contacts', 'Recipients', 'Mailer', 'Properties');
        if (!in_array($action, $actions)) {
            $action = 'Contacts';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if ($this->gadget->GetPermission('ManageContacts')) {
            $menubar->AddOption('Contacts',
                                $this::t('TITLE'),
                                BASE_SCRIPT . '?reqGadget=Contact&amp;reqAction=Contacts',
                                'gadgets/Contact/Resources/images/contact_mini.png');
        }
        if ($this->gadget->GetPermission('ManageRecipients')) {
            $menubar->AddOption('Recipients',
                                $this::t('RECIPIENTS'),
                                BASE_SCRIPT . '?reqGadget=Contact&amp;reqAction=Recipients',
                                'gadgets/Contact/Resources/images/recipients_mini.png');
        }
        if ($this->gadget->GetPermission('AccessToMailer')) {
            $menubar->AddOption('Mailer',
                                $this::t('MAILER'),
                                BASE_SCRIPT . '?reqGadget=Contact&amp;reqAction=Mailer',
                                'gadgets/Contact/Resources/images/email_send.png');
        }
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $menubar->AddOption('Properties',
                                Jaws::t('PROPERTIES'),
                                BASE_SCRIPT . '?reqGadget=Contact&amp;reqAction=Properties',
                                'gadgets/Contact/Resources/images/properties_mini.png');
        }

        $menubar->Activate($action);
        return $menubar->Get();
    }

}