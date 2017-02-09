<?php
/**
 * Contact - ACL hook
 *
 * @category    GadgetHook
 * @package     Contact
 */
class Contact_Hooks_ACL extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of dynamic ACL keys
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $language = $this->gadget->registry->fetch('admin_language', 'Settings');
        $recipients = $this->gadget->model->load('Recipients')->GetRecipients();
        if (!Jaws_Error::IsError($recipients)) {
            foreach ($recipients as $recipient) {
                $this->gadget->translate->insert(
                    'ACL_MANAGERECIPIENTCONTACTS_'. $recipient['id'],
                    _t('CONTACT_ACL_MANAGERECIPIENTCONTACTS', $recipient['name'])
                );
            }
        }

    }

}