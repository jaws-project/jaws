<?php
/**
 * PrivateMessage - Search gadget hook
 *
 * @category    GadgetHook
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets search fields of the gadget
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
            'pm_messages' => array('subject', 'body'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return array();
        }

        $objORM->table('pm_messages');
        $objORM->select('id', 'from', 'folder', 'subject', 'body', 'insert_time');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $objORM->openWhere()->openWhere('from', $user);
        $objORM->and()->where('to', 0);
        $objORM->closeWhere()->or()->where('to', $user);
        $objORM->closeWhere()->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('id')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $messages = array();
        foreach ($result as $m) {
            $message = array();
            $message['title'] = $m['subject'];

            if ($m['from'] == $user && $m['folder'] == PrivateMessage_Info::PRIVATEMESSAGE_FOLDER_DRAFT) {
                $url = $this->gadget->urlMap('Compose', array('id' => $m['id']));
            } else {
                $url = $this->gadget->urlMap(
                    'Message',
                    array('id'  => $m['id']));
            }

            $message['url']     = $url;
            $message['image']   = 'gadgets/PrivateMessage/Resources/images/logo.png';
            $message['snippet'] = $m['body'];
            $message['date']    = $m['insert_time'];

            $stamp              = str_replace(array('-', ':', ' '), '', $m['insert_time']);
            $messages[$stamp]   = $message;
        }

        return $messages;
    }

}