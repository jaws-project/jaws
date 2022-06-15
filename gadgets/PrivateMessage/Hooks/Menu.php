<?php
/**
 * PrivateMessage - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     PrivateMessage
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('PrivateMessage'),
            'title' => $this::t('ACTIONS_PRIVATEMESSAGE')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX)
            ),
            'title' => $this::t('INBOX')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT)
            ),
            'title' => $this::t('DRAFT')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED)
            ),
            'title' => $this::t('ARCHIVED')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX)
            ),
            'title' => $this::t('OUTBOX')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH)
            ),
            'title' => $this::t('TRASH')
        );
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Messages'),
            'title' => $this::t('ALL_MESSAGES')
        );
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Compose'),
            'title' => $this::t('COMPOSE_MESSAGE')
        );
        return $urls;
    }

}