<?php
/**
 * PrivateMessage - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
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
            'title' => _t('PRIVATEMESSAGE_ACTIONS_PRIVATEMESSAGE')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX)
            ),
            'title' => _t('PRIVATEMESSAGE_INBOX')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT)
            ),
            'title' => _t('PRIVATEMESSAGE_DRAFT')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED)
            ),
            'title' => _t('PRIVATEMESSAGE_ARCHIVED')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX)
            ),
            'title' => _t('PRIVATEMESSAGE_OUTBOX')
        );
        $urls[] = array(
            'url' => $this->gadget->urlMap(
                'Messages',
                array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH)
            ),
            'title' => _t('PRIVATEMESSAGE_TRASH')
        );
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Messages'),
            'title' => _t('PRIVATEMESSAGE_ALL_MESSAGES')
        );
        $urls[] = array(
            'url'   => $this->gadget->urlMap('Compose'),
            'title' => _t('PRIVATEMESSAGE_COMPOSE_MESSAGE')
        );
        return $urls;
    }

}