<?php
/**
 * KaveNegarSOAP notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_KaveNegarSOAP extends Jaws_Notification
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'KaveNegar SOAP';


    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type = Jaws_Notification::SMS_DRIVER;

    /**
     * Options
     * @var     array
     * @access  protected
     */
    protected $options = array(
        'apikey' => '',
        'sender' => '',
    );

    /**
     * Store soap object instance
     * @var     array
     * @access  private
     */
    private $soapClient;

    /**
     * constructor
     *
     * @access  protected
     * @param   array   $options    Associated options array
     */
    protected function __construct($options = array())
    {
        if (!extension_loaded('soap')) {
            return Jaws_Error::raiseError('SOAP extension is not available.', __CLASS__);
        }

        parent::__construct();
        $this->options = $options;
        $this->soapClient = new SoapClient(
            //'http://api.kavenegar.com/soap/v1.asmx?WSDL',
            ROOT_JAWS_PATH . 'include/Jaws/Notification/KaveNegarSOAP.wsdl',
            array('encoding' => 'UTF-8', 'exceptions' => 0)
        );
    }


    /**
     * Get options list
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function getDriverOptions()
    {
        return array_merge(
            array(
                'apikey' => '',
                'sender' => '',
            ),
            $this->options
        );
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
        $tpl = new Jaws_Template(true);
        $tpl->loadRTLDirection = $this->attributes['site_direction'] == 'rtl';
        $tpl->Load('Notification.html', 'include/Jaws/Resources');
        $tpl->SetBlock('notification');
        $tpl->SetBlock('notification/sms');
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
        $tpl->ParseBlock('notification/sms');
        $tpl->ParseBlock('notification');
        $message = $tpl->Get();
        unset($tpl);

        $result = $this->soapClient->SendSimpleByApikey(
            array(
                'apikey'   => $this->options['apikey'],
                'sender'   => $this->options['sender'],
                'message'  => $message,
                'receptor' => $contacts,
                'unixdate' => 0,
                'msgmode'  => 1,
                'status'   => 0,
                'statusmessage' => ''
            )
        );
        //uniqid(mt_rand(), true)

        if (is_soap_fault($result)) {
            return Jaws_Error::raiseError(
                "KaveNegarSMS error [{$result->faultcode}]: {$result->faultstring}",
                Notification_Info::MESSAGE_STATUS_REJECTED
            );
        }

        if ($result->status != 200) {
            return Jaws_Error::raiseError(
                Jaws_XSS::filter($result->statusmessage),
                ($result->status == 409)? Notification_Info::MESSAGE_STATUS_PENDING : Notification_Info::MESSAGE_STATUS_REJECTED
            );
        }

        return true;
    }

}