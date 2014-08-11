<?php
/**
 * Mail notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_Mail
{
    /**
     * Store mail object instance
     * @var     array
     * @access  private
     */
    private $object;


    /**
     * constructor
     *
     * @access  public
     * @param   array   $options    Associated options array
     * @return  void
     */
    public function __construct($options)
    {
        $this->object = new Jaws_Mail;
        $this->object->SetFrom();
    }


    /**
     * Sends notify to user
     *
     * @access  public
     * @param   array   $user           User's properties associated array
     * @param   string  $title          Notification title
     * @param   string  $summary        Notification summary
     * @param   string  $description    Notification description
     * @return  mixed   Jaws_Error on failure
     */
    function notify($user, $title, $summary, $description)
    {
        $this->object->AddRecipient($user['email']);
        $this->object->SetSubject($title);
        $this->object->SetBody($description);
        return $this->object->send();
    }

}