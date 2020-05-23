<?php
/**
 * Web push notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_WebPush extends Jaws_Notification
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Jaws WebPush API';

    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type = Jaws_Notification::WEB_DRIVER;

    /**
     * Store $webPush object instance
     * @var     array
     * @access  private
     */
    private $webPush;

    /**
     * constructor
     *
     * @access  protected
     * @param   array $options Associated options array
     */
    protected function __construct($options = array())
    {
        parent::__construct();

        require_once ROOT_JAWS_PATH . 'libraries/php/WebPush/WebPush.php';
        require_once ROOT_JAWS_PATH . 'libraries/php/WebPush/Subscription.php';

        $auth = array(
            'VAPID' => array(
                'subject' => $this->app->getSiteURL('/', false),
                'publicKey' => Jaws_Gadget::getInstance('Notification')->registry->fetch('webpush_pub_key'),
                'privateKey' => Jaws_Gadget::getInstance('Notification')->registry->fetch('webpush_pvt_key'),
            ),
        );

        $this->webPush = new WebPush($auth);
    }

    /**
     * Get options list
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function getDriverOptions()
    {
        return array();
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
        $shouter, $name, $contacts, $title, $summary, $verbose, $variables, $time, $callback_url, $image
    ) {
        try {
            foreach ($contacts as $pushSubscription) {
                $pushSubscription = @unserialize($pushSubscription);
                if (!empty($pushSubscription)) {
                    $objSubscription = Subscription::create($pushSubscription);
                    $res = $this->webPush->sendNotification(
                        $objSubscription,
                        json_encode(
                            array(
                                'icon'    => $image,
                                'url'     => $callback_url,
                                'title'   => $title,
                                'body'    => $summary,
                                'vibrate' => [],
                            )
                        )
                    );
                }
            }

            $this->webPush->flush();
        } catch (Exception $error) {
            return $error;
        }

        return true;
    }

}