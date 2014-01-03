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
                    array('m.[subject]', 'm.[body]'),
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
               m.[id] as m_id ,m.[user], m.[subject], m.[body], m.[insert_time], r.[id] as r_id
            FROM [[pm_messages]] m
            INNER JOIN [[pm_recipients]] r ON r.[message] = m.[id]
            WHERE
                (m.[user] = {user} OR ((r.[recipient] = {user} OR r.[recipient] = 0) AND m.[published] = {published}))
            ';

        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY m.[insert_time] desc';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $messages = array();
        foreach ($result as $m) {
            $message = array();
            $message['title'] = $m['subject'];

            if($m['user']==$user) {
                $url =  $this->gadget->urlMap('Compose', array('id' => $m['m_id']));
            } else {
                $url = $this->gadget->urlMap(
                    'PrivateMessage',
                    'InboxMessage',
                    array('id'  => $m['r_id']));
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