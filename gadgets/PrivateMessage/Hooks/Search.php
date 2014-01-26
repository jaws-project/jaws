<?php
/**
 * PrivateMessage - Search gadget hook
 *
 * @category    GadgetHook
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
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
                    array('[subject]', '[body]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql   Prepared search(WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return array();
        }

        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $params = array();
        $params['user'] = $user;
        $params['published'] = true;

        $sql = '
            SELECT
               [id], [from], [folder], [subject], [body], [insert_time]
            FROM [[pm_messages]]
            WHERE
                (([from] = {user} and [to] = 0) OR ([to] = {user}))
            ';

        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY [insert_time] desc';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return array();
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