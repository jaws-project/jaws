<?php
/**
 * Calendar.php - Calendar Widget
 *
 * @version  $Id $
 * @author   Jorge A Gallegos <kad@gulags.org>
 *
 * <c> Jonathan Hernandez 2004
 * <c> Pablo Fischer 2004
 * <c> Jorge A Gallegos 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/Entry.php';

define('CALENDAR_REQ_PARAMS', 1);
class Calendar extends Entry
{
    /**
     * Calendar Date: the date of the calendar
     *
     * @var    string $_date
     * @access private
     */
    var $_date;

    /**
     * Is it required?
     *
     * @var      string $_isRequired
     * @access   private
     * @see      setRequired, isRequired
     */
    var $_isRequired;

    /**
     * Display week number?
     *
     * @var      boolean $_displayWeek
     * @access   private
     * @see      setDisplayWeekNumber
     */
    var $_displayWeek;

    /**
     * Display today highlighted?
     *
     * @var      boolean $_displayWeek
     * @access   private
     * @see      setDisplayToday
     */
    var $_displayToday;

    /**
     * Week begins on monday (1) or sunday (0)?
     *
     * @var      boolean $_startDay
     * @access   private
     * @see      setStartDay
     */
    var $_startDay;

    /**
     * Public constructor
     *
     * @param   string Name of the calendar
     * @param   string Date of the calendar
     * @param   string Title of the calendar
     * @access  public
     */
    function __construct($name, $date = '', $title = '')
    {
        $this->_name        = $name;
        $this->_title       = $title;
        $this->_date        = $date;
        $this->_displayWeek = 'false';
        $this->_displayToday = 'false';
        $this->_startDay    = 'false';

        $this->AvailableEvents = array('onfocus');
        parent::init();
    }

    /**
     * Set the required status
     *
     * @param   boolean status
     * @access  public
     */
    function setRequired($status)
    {
        $this->_isRequired = $status;
    }

    /**
     * Get the required status
     *
     * @return  boolean
     * @access  public
     */
    function isRequired()
    {
        return $this->_isRequired;
    }

    /**
     * Set the week number display property
     * @return void
     * @accecss public
     * @param $flag boolean
     */
     function setDisplayWeekNumber($flag)
     {
        if ($flag) {
            $this->_displayWeek = 'true';
        } else {
            $this->_displayWeek = 'false';
        }
     }

    /**
     * Set the week number display property
     * @return void
     * @accecss public
     * @param $flag boolean
     */
     function setDisplayToday($flag)
     {
        if ($flag) {
            $this->_displayToday = 'true';
        } else {
            $this->_displayToday = 'false';
        }
     }

    /**
     * Set the week starting day
     * @return void
     * @accecss public
     * @param $flag boolean
     */
     function setStartDay($flag)
     {
        if ($flag) {
            $this->_startDay = 'true';
        } else {
            $this->_startDay = 'false';
        }
     }

     function addHoliday($d, $m, $y, $desc)
     {
        $escape_desc = addslashes($desc);
        $this->addEvent(new JSEvent(ON_FOCUS, "addHoliday ($d, $m, $y, '$escape_desc');"));
     }

    /**
     * Build the XHTML data
     *
     * @access  private
     */
    function buildXHTML ()
    {
        $this->addFile(PIWI_URL . 'piwidata/js/calendar.js');
        $this->addEvent(new JSEvent(ON_FOCUS,
                                    "popUpCalendar(this, this, 'dd/mm/yyyy', " .
                                    $this->_displayToday . ", " .
                                    $this->_displayWeek . ", " .
                                    $this->_startDay . ")"));
        $this->addEvent(new JSEvent(ON_FOCUS, "this.blur()"));
        $this->setReadOnly(true);
        parent::buildXHTML();
    }
}
?>
