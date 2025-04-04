<?php
/**
 * Mail notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_Mail extends Jaws_Notification
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Jaws Mailer';

    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type = Jaws_Notification::EML_DRIVER;

    /**
     * Store mail object instance
     * @var     array
     * @access  private
     */
    private $object;

    /**
     * constructor
     *
     * @access  protected
     * @param   array $options Associated options array
     */
    protected function __construct($options = array())
    {
        parent::__construct();
        $this->object = Jaws_Mail::getInstance('notification');
    }


    /**
     * Sends notify to user
     *
     * @access  public
     * @param   string  $shouter        Shouter(gadget) name
     * @param   string  $name           Notification type name
     * @param   array   $contacts       Contacts array
     * @param   string  $title          Title
     * @param   string  $summary        Summary
     * @param   string  $verbose        Verbose
     * @param   array   $variables      Variables
     * @param   integer $time           Time of notify(timestamps)
     * @param   string  $callback_url   Notification callback URL
     * @param   string  $image          Notification image
     * @return  mixed   Jaws_Error on failure
     */
    function notify(
        $shouter, $name, $contacts, $title, $summary, $verbose, array $variables, $time, $callback_url, $image
    ) {
        $this->object->reset();
        $this->object->SetFrom();
        foreach ($contacts as $email) {
            $this->object->AddRecipient($email);
        }
        $this->object->SetSubject($title);

        $tpl = new Jaws_Template(true);
        $tpl->loadRTLDirection = $this->attributes['site_direction'] == 'rtl';
        $tpl->Load('Notification.html', 'include/Jaws/Resources');
        $tpl->SetBlock('notification');
        $tpl->SetBlock('notification/eml');
        $tpl->SetVariable('site-url',       $this->attributes['site_url']);
        $tpl->SetVariable('site-direction', $this->attributes['site_direction']);
        $tpl->SetVariable('site-name',      $this->attributes['site_name']);
        $tpl->SetVariable('site-slogan',    $this->attributes['site_slogan']);
        $tpl->SetVariable('site-comment',   $this->attributes['site_comment']);
        $tpl->SetVariable('site-author',    $this->attributes['site_author']);
        $tpl->SetVariable('site-license',   $this->attributes['site_license']);
        $tpl->SetVariable('site-copyright', $this->attributes['site_copyright']);
        $tpl->SetVariable('title', $title);
        $tpl->SetVariable('summary', $this->setMessageVariables($summary, $variables));
        $tpl->SetVariable('content', $this->setMessageVariables($verbose, $variables));
        $tpl->SetVariablesArray(Jaws_Date::getInstance()->GetDateInfo($time));
        $tpl->ParseBlock('notification/eml');
        $tpl->ParseBlock('notification');
        $this->object->SetBody($tpl->Get());
        unset($tpl);

        $result = $this->object->send();
        if (Jaws_Error::IsError($result)) {
            return Jaws_Error::raiseError(
                $result->getMessage(),
                Notification_Info::MESSAGE_STATUS_REJECTED
            );
        }

        return true;
    }

}