<?php
/**
 * SMS notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_SMS
{
    const DRIVER_TYPE = 'sms';

    /**
     * Store mail object instance
     * @var     array
     * @access  private
     */
    private $object;

    /**
     * Site attributes
     *
     * @access  private
     * @var     array
     */
    private $attributes = array();


    /**
     * constructor
     *
     * @access  public
     * @param   array   $options    Associated options array
     */
    public function __construct($options)
    {
    }


    /**
     * Sends notify to user
     *
     * @access  public
     * @param   array   $users      Users properties associated array
     * @param   string  $title      Notification title
     * @param   string  $summary    Notification summary
     * @param   string  $content    Notification content
     * @return  mixed   Jaws_Error on failure
     */
    function notify($users, $title, $summary, $content)
    {
    }

}