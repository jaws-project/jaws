<?php
/**
 * Notification base class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Drivers notification type
     */
    const EML_DRIVER = 1; // Email
    const SMS_DRIVER = 2; // SMS
    const WEB_DRIVER = 3; // Web push API

    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title;

    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type;

    /**
     * Driver configuration options
     *
     * @access  protected
     * @var     array
     */
    protected $options;

    /**
     * Site attributes
     *
     * @access  private
     * @var     array
     */
    protected $attributes = array();

    /**
     * constructor
     *
     * @access  public
     * @param   array $options Associated options array
     */
    protected function __construct($options = array())
    {
        $this->app = Jaws::getInstance();

        // fetch all registry keys related to site attributes
        $this->attributes = $this->app->registry->fetchAll('Settings', false);
        Jaws_Translate::getInstance()->LoadTranslation(
            'Global',
            JAWS_COMPONENT_OTHERS,
            $this->attributes['site_language']
        );
        $this->attributes['site_url']       = $this->app->GetSiteURL('/');
        $this->attributes['site_direction'] = _t_lang($this->attributes['site_language'], 'GLOBAL_LANG_DIRECTION');
    }


    /**
     * An interface for available drivers
     *
     * @access  public
     * @param   string  $driver     Notification driver name
     * @param   array   $options    Associated options array
     * @return  object  Jaws_Notification type object or Jaws_Error on failure
     */
    static function getInstance($driver, $options)
    {
        static $instances = array();
        $driver = preg_replace('/[^[:alnum:]_\-]/', '', $driver);
        if (!isset($instances[$driver])) {
            $driverFile = ROOT_JAWS_PATH . "include/Jaws/Notification/$driver.php";
            if (!file_exists($driverFile)) {
                return Jaws_Error::raiseError('Loading notification driver failed.', __CLASS__);
            }

            include_once $driverFile;
            $className = 'Jaws_Notification_' . $driver;
            $instances[$driver] = new $className($options);
        }
        
        return $instances[$driver];
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
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
     * @param   integer $time           Time of notify(timestamps)
     * @param   string  $callback_url   Notification callback URL
     * @param   string  $image          Notification image
     * @return  mixed   Jaws_Error on failure
     */
    function notify($shouter, $name, $contacts, $title, $summary, $verbose, $time, $callback_url, $image)
    {
        return Jaws_Error::raiseError('notify() method not supported by this driver.', __CLASS__);
    }

}