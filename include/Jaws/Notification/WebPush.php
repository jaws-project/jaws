<?php
/**
 * Web push notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

require_once JAWS_PATH . 'libraries/vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class Jaws_Notification_WebPush extends Jaws_Notification
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Jaws Web push API';

    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type = Jaws_Notification::WP_DRIVER;

    /**
     * Store $auth object instance
     * @var     array
     * @access  private
     */
    private $auth;

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

        if (!empty($options['server_public_key'])) {
            $this->auth = array(
                'VAPID' => array(
                    'subject' => $GLOBALS['app']->GetSiteURL('/'),
                    'publicKey' => $options['server_public_key'],
                    'privateKey' => $options['server_private_key'], // in the real world, this would be in a secret file
                ),
            );

            $this->webPush = new WebPush($this->auth);
        }
    }

    /**
     * Get options list
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function getDriverOptions()
    {
        return array(
            'server_private_key',
            'server_public_key',
        );
    }

    /**
     * Sends notify to user
     *
     * @access  public
     * @param   array   $contacts    Contacts array
     * @param   string  $title       Notification title
     * @param   string  $summary     Notification summary
     * @param   string  $content     Notification content
     * @param   string  $url         Notification URL
     * @param   string  $icon        Notification icon
     * @param   string  $image       Notification image
     * @param   integer $time        Time of notify(timestamps)
     * @return  mixed   Jaws_Error on failure
     * @throws ErrorException
     */
    function notify($contacts, $title, $summary, $content, $url, $icon, $image, $time)
    {
        $dir = _t_lang( $GLOBALS['app']->Registry->fetch('site_language', 'Settings'), 'GLOBAL_LANG_DIRECTION');
        $notifyContent = array(
            'title' => $summary,
            'body' => $content,
            'dir' => $dir,
            'requireInteraction' => 'true',
            'icon' => $icon,
            'image' => $image,
            'url' => $url,
            'tag' => 'iic',
        );

        foreach ($contacts as $subscriber) {
            $subscriberContent = unserialize($subscriber);

//            $trans = array(
//                'https://fcm.googleapis.com' => 'http://localhost:9090',
//                'https://updates.push.services.mozilla.com' => 'http://localhost:9091'
//            );
            $subscriberContent['endpoint'] = strtr($subscriberContent['endpoint'], $trans);

            $subscription = Subscription::create($subscriberContent);

            $res = $this->webPush->sendNotification(
                $subscription,
                json_encode($notifyContent),
                true
            );
        }

        return $res;
    }

}