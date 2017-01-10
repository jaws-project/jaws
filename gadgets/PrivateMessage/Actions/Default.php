<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Default extends Jaws_Gadget_Action
{
    /**
     * Displays menu bar according to selected action
     *
     * @access  public
     * @param   string  $action_selected    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action_selected)
    {
        $tpl = $this->gadget->template->load('Menubar.html');
        $tpl->SetBlock('menubar');

        $actions = array(
            'Notifications' => array(
                'title' => _t('PRIVATEMESSAGE_NOTIFICATIONS'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/notify.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_NOTIFICATIONS))
            ),
            'Inbox' => array(
                'title' => _t('PRIVATEMESSAGE_INBOX'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/inbox.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_INBOX))
            ),
            'Outbox' => array(
                'title' => _t('PRIVATEMESSAGE_OUTBOX'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/outbox.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_OUTBOX))
            ),
            'Draft' => array(
                'title' => _t('PRIVATEMESSAGE_DRAFT'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/draft.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT))
            ),
            'Archived' => array(
                'title' => _t('PRIVATEMESSAGE_ARCHIVED'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/archive.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_ARCHIVED))
            ),
            'Trash' => array(
                'title' => _t('PRIVATEMESSAGE_TRASH'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/trash.png',
                'url' => $this->gadget->urlMap(
                    'Messages',
                    array('folder' => PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_TRASH))
            ),
            'Compose' => array(
                'title' => _t('PRIVATEMESSAGE_COMPOSE_MESSAGE'),
                'icon' => 'gadgets/PrivateMessage/Resources/images/compose.png',
                'url' => $this->gadget->urlMap('Compose')
            ),
        );

        foreach ($actions as $action => $data) {
            $tpl->SetBlock('menubar/item');
            $tpl->SetVariable('action', $action);
            $tpl->SetVariable('title', $data['title']);
            $tpl->SetVariable('icon', $data['icon']);
            $tpl->SetVariable('url', $data['url']);
            $tpl->SetVariable('selected', '');
            if ($action_selected == $action) {
                $tpl->SetVariable('selected', 'selected');
            }
            $tpl->ParseBlock('menubar/item');

        }

        $tpl->ParseBlock('menubar');
        return $tpl->Get();
    }

}