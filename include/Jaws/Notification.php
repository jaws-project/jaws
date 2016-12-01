<?php
/**
 * Notification base class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification
{
    /**
     * Drivers notification type
     */
    const EML_DRIVER = 0;
    const SMS_DRIVER = 1;

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
        // fetch all registry keys related to site attributes
        $this->attributes = $GLOBALS['app']->Registry->fetchAll('Settings', false);
        Jaws_Translate::getInstance()->LoadTranslation(
            'Global',
            JAWS_COMPONENT_OTHERS,
            $this->attributes['site_language']
        );
        $this->attributes['site_url']       = $GLOBALS['app']->GetSiteURL('/');
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
            $driverFile = JAWS_PATH . "include/Jaws/Notification/$driver.php";
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
     * @param   array   $contacts   Contacts array
     * @param   string  $title      Notification title
     * @param   string  $summary    Notification summary
     * @param   string  $content    Notification content
     * @param   integer $time   Time of notify(timestamps)
     * @return  mixed   Jaws_Error on failure
     */
    function notify($contacts, $title, $summary, $content, $time)
    {
        return Jaws_Error::raiseError('notify() method not supported by this driver.', __CLASS__);
    }

}