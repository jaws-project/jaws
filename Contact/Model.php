<?php
/**
 * Contact admin model
 *
 * @category   GadgetModel
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Model extends Jaws_Gadget_Model
{
    /**
     * Get information of a Contact
     *
     * @access  public
     * @param   int     $id     Contact ID
     * @return  mixed   Array of Contact Information or Jaws_Error on failure
     */
    function GetContact($id)
    {
        $sql = '
            SELECT
                [id], [ip], [name], [email], [company], [url], [tel], [fax], [mobile], [address],
                [recipient], [subject], [msg_txt], [attachment], [createtime], [updatetime]
            FROM [[contacts]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get information of one Recipient
     *
     * @access  public
     * @param   string  $id     ID of the Recipient
     * @return  array  Array with the information of a Recipient or Jaws_Error on failure
     */
    function GetRecipient($id)
    {
        $sql = '
            SELECT
                [id], [name], [email], [tel], [fax], [mobile], [inform_type], [visible]
            FROM [[contacts_recipients]]
            WHERE [id] = {id}';

        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'), _t('CONTACT_NAME'));
    }

    /**
     * Get a list of the available Recipients
     *
     * @access  public
     * @param   bool    $onlyVisible
     * @param   bool    $limit
     * @param   bool    $offset
     * @return  mixed   Array of Recipients or Jaws_Error on failure
     */
    function GetRecipients($onlyVisible = false, $limit = false, $offset = null)
    {
        if (is_numeric($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        if ($onlyVisible) {
            $sql = '
                SELECT
                    [id], [name], [email], [tel], [fax], [mobile]
                FROM [[contacts_recipients]]
                WHERE [visible] = {visible}
                ORDER BY [id] ASC';
        } else {
            $sql = '
                SELECT
                    [id], [name], [email], [tel], [fax], [mobile], [visible]
                FROM [[contacts_recipients]]
                ORDER BY [id] ASC';
        }

        $params = array();
        $params['visible'] = 1;

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Sends email to user
     *
     * @access  public
     * @param   string  $name       Name
     * @param   string  $email      Email address
     * @param   string  $company
     * @param   string  $url
     * @param   string  $tel
     * @param   string  $fax
     * @param   string  $mobile
     * @param   string  $address
     * @param   string  $rcipient   Rcipient ID
     * @param   string  $subject    Subject of message
     * @param   string  $attachment Attachment filename
     * @param   string  $message    Message content
     * @return  bool    True on Success or False on Failure
     */
    function InsertContact($name, $email, $company, $url, $tel, $fax, $mobile,
                           $address, $rcipient, $subject, $attachment, $message)
    {
        $sql = "
            INSERT INTO [[contacts]]
                ([user], [ip], [name], [email], [company], [url], [tel], [fax], [mobile], [address], [recipient],
                 [subject], [attachment], [msg_txt], [reply], [reply_sent], [createtime], [updatetime])
            VALUES
                ({user}, {ip}, {name}, {email}, {company}, {url}, {tel}, {fax}, {mobile}, {address}, {rcipient},
                 {subject}, {attachment}, {message}, {reply}, {reply_sent}, {now}, {now})";

        $params = array();
        $params['user']       = $GLOBALS['app']->Session->GetAttribute('user');
        $params['ip']         = $_SERVER['REMOTE_ADDR'];
        $params['name']       = $name;
        $params['email']      = $email;
        $params['company']    = $company;
        $params['url']        = $url;
        $params['tel']        = $tel;
        $params['fax']        = $fax;
        $params['mobile']     = $mobile;
        $params['address']    = $address;
        $params['rcipient']   = (int)$rcipient;
        $params['subject']    = $subject;
        $params['attachment'] = $attachment;
        $params['message']    = $message;
        $params['reply']      = '';
        $params['reply_sent'] = 0;
        $params['now']      = $GLOBALS['db']->Date();

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $GLOBALS['app']->Session->SetCookie('visitor_name',  $name,  60*24*150);
        $GLOBALS['app']->Session->SetCookie('visitor_email', $email, 60*24*150);
        $GLOBALS['app']->Session->SetCookie('visitor_url',   $url,   60*24*150);

        return true;
    }

}