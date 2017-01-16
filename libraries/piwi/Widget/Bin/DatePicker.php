<?php
/**
 * DatePicker.php - Widget that displays a flat calendar to select
 * a date
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2005
 * <c> Piwi
*/
require_once PIWI_PATH . '/Widget/Bin/Bin.php';
require_once PIWI_PATH . '/Widget/Bin/Button.php';
require_once PIWI_PATH . '/Widget/Bin/Entry.php';
require_once PIWI_PATH . '/Widget/Bin/ImageStocks.php';

/**
 * Event onupdate
 */
define('ON_UPDATE', 'onupdate');
define('ON_CLOSE',  'onclose');

define('DATEPICKER_REQ_PARAMS', 1);
class DatePicker extends Bin
{
    /**
     * Read Only Property
     *
     * @access private
     * @var    boolean
     */
    var $_readOnly = false;

    /**
     * CSS theme, can be any of valid themes
     *
     *
     * By default blue is used.
     *
     * @access  private
     * @var     string
     */
    var $_theme = 'blue';
    
    /**
     * Include the theme file? This is useful if developer wants
     * to include his own CSS file
     *
     * Default value: true.
     *
     * @access  private
     * @var     boolean
     */
    var $_includeCSS = true;
    
    /**
     * Include the js file? This is useful if developer wants
     *
     * Default value: true.
     *
     * @access  private
     * @var     boolean
     */
    var $_includeJS = true;

    /**
     * Valid CSS themes:
     *
     * blue2, blue, brown, green, system, tas,
     * win2k-1, win2k-2, win2k-cold-1, win2k-cold-2
     *
     * @access  private
     * @var     array
     */
    var $_validThemes = array('blue2',  'blue', 'brown',   'green', 
                              'system', 'tas',  'win2k-1', 'win2k-2');

    /**
     * Calendar type
     *
     * @access   private
     * @var      string
     * @see      setCalType
     */
    var $_calType = '';

    /**
     * Language code
     *
     * @access   private
     * @var      string
     * @see      setLanguageCode
     */
    var $_langCode = 'en';

     /**
     * Language file
     *
     * @access   private
     * @var      string
     * @see      setLanguageFile
     */
    var $_langFile = '';

    /**
     * Button text
     *
     * @access   private
     * @var      string
     * @see      setButtonText
     */
    var $_buttonText;

    /**
     * Button icon
     *
     * @access   private
     * @var      string
     * @see      setButtonIcon
     */
    var $_buttonIcon;

    /**
     * Event: onClose action.
     *
     * @access   private
     * @var      string
     */
    var $_onCloseEvent = '';

    /**
     * Event: onUpdate action.
     *
     * @access   private
     * @var      string
     */
    var $_onUpdateEvent = '';

    /**
     * Event: onSelect date ation.
     *
     * @access   private
     * @var      string
     */
    var $_onSelectEvent = '';

    /**
     * Date Format, in UNIX format
     *
     * @access   private
     * @var      string
     * @see      setDateFormat
     */
    var $_dateFormat = "%Y-%m-%d";

    /**
     * Show week numbers?
     *
     * @access   private
     * @var      boolean
     * @see      showWeekNumbers
     */
    var $_showWeekNumbers = false;

    /**
     * First day of week. Starting in 0 = sunday
     *
     * @access   private
     * @var      int
     * @see      setFirstDay
     */
    var $_firstDay = null;

    /**
     * Initially selected date
     *
     * @access   private
     * @var      string
     * @see      setInitialDate
     */
    var $_initDate = '';

    /**
     * Also a time picker
     *
     * @access   private
     * @var      boolean
     * @see      showTimePicker
     */
    var $_showTimePicker = false;

    /**
     * Select multiple dates
     *
     * @access   private
     * @var      boolean
     * @see      selectMultipleDates
     */
    var $_selectMultipleDates = false;

    /**
     * Selected multiple dates
     *
     * @access   private
     * @var      boolean
     * @see      setSelectedDates
     */
    var $_selectedDates = array();

    /**
     * Date entry
     *
     * @access   private
     * @var      Entry
     */
    var $_entry;

    /**
     * Date button
     *
     * @access   private
     * @var      Button
     */
    var $_button;

    /**
     * Public constrcutor
     *
     * @param   string   $name   Name of calendar field
     * @param   string   $value  Value of calendar field
     * @param   string   $text   Text in the button
     * @param   string   $stock  Stock image (the button image)
     * @access  public
     */
    function DatePicker($name, $value = '', $text = '', $stock = '')
    {
        if (empty($stock)) {
            $stock = STOCK_CALENDAR;
        }
        $this->_tableID       = $name.'_table';
        $this->_value         = $value;
        $this->_buttonText    = $text;
        $this->_buttonIcon    = $stock;
        $this->_selectedDates = array();
        $this->_button        = new Button($name . '_button', $text, $stock);
        $this->_entry         = new Entry($name, $value);
        $this->_calType       = 'gregorian';
        $this->_langFile      = PIWI_URL . 'piwidata/js/jscalendar/lang/calendar-en.js';

        $this->_availableEvents = array("onselect", "onclose", "onupdate", "onchange");
        parent::init();
    }

    /**
     * Set the id of the widget
     *
     * @access   public
     */
    function setID($id)
    {
        $this->_entry->setID($id);
        $this->_button->setID($id . '_button');
        parent::setID($id . '_table');
    }

    /**
     * Set the read-only property
     *
     * @access public
     * @param  string $flag  True for read-only
     */
    function setReadOnly($flag = true)
    {
        $this->_readOnly = $flag;
    }

    /**
     * Set the theme
     *
     * @access  public
     * @param   string  $theme  Theme to be used (should be valid)
     */
    function setTheme($theme)
    {
        if (in_array($theme, $this->_validThemes)) {
            $this->_theme = $theme;
        } else {
            die('[PIWI] - Theme '.$theme.' is not valid');
        }
    }

    /**
     * Set the calendar type
     *
     * @param   string  $cal    Calendar name
     * @access  public
     */
    function setCalType($cal)
    {
        $this->_calType = strtolower($cal);
        $this->_entry->setData('cal', strtolower($cal));
    }

    /**
     * Set the language code
     *
     * @param   string  $code     Language code
     * @param   string  $useUtf8  Language should be in UTF8
     * @access  public
     */
    function setLanguageCode($code, $useUtf8 = false)
    {
        $code = strtolower($code);

        $codes = array('af', 'al', 'bg', 'big5',
                       'br', 'ca', 'cs', 'da',
                       'de', 'du', 'el', 'en',
                       'es', 'fa', 'fi', 'fr',
                       'he', 'hr', 'hu', 'it',
                       'jp', 'ko', 'lt', 'lv',
                       'nl', 'no', 'pl', 'pt',
                       'ro', 'ru', 'si', 'sk',
                       'sp', 'sv', 'tr', 'zh');
        if (!in_array($code, $codes)) {
            die('[PIWI] - Language code '.$code.' is not valid');
        }

        $code = 'calendar-'.$code;

        $file = '';
        if ($useUtf8) {
            $file = PIWI_URL . 'piwidata/js/jscalendar/lang/'.$code.'.utf8.js';
        } else {
            $file = PIWI_URL . 'piwidata/js/jscalendar/lang/'.$code.'.js';
        }
        $this->_langFile = $file;
    }

    /**
     * Set date format
     *
     * @param  string    $format  Date Format
     * @access public
     */
    function setDateFormat($format)
    {
        $this->_dateFormat = $format;
        $this->_entry->setData('format', $format);
    }

    /**
     * Set initial date
     *
     * @param  string    $date Initial date
     * @access public
     */
    function setInitialDate($date)
    {
        $this->_initDate = $date;
    }

    /**
     * Set the button text
     *
     * @param  string    $text  Button text
     * @access public
     */
    function setButtonText($text)
    {
        $this->_buttonText = $text;
        $this->_button->setValue($text);
    }

    /**
     * Set the button icon
     *
     * @param  string    $icon Button icon
     * @access public
     */
    function setButtonIcon($icon)
    {
        $this->_buttonIcon = $icon;
        $this->_button->setStock($icon);
    }

    /**
     * Show the week numbers?
     *
     * @param   boolean   $status True or false
     * @access  public
     */
    function showWeekNumbers($status = true)
    {
        if (is_bool($status)) {
            $this->_showWeekNumbers = $status;
        } else {
            $this->_showWeekNumbers = true;
        }
    }

    /**
     * Set the first day of the week.
     *
     * 0 = Sunday
     * 1 = Monday
     * 2 = Tuesday
     * 3 = Wednesday
     * 4 = Thursday
     * 5 = Friday
     * 6 = Saturday
     *
     * @param   int   $day   Day number
     * @access  public
     */
    function setFirstDay($day)
    {
        if ($day >= 0 && $day <= 6) {
            $this->_firstDay = $day;
        } else {
            die('[PIWI] - Calendar day should be between 0 and 6');
        }
    }

    /**
     * Select multiple days?
     *
     * @param   boolean  $status  True or false
     * @access  public
     */
    function selectMultipleDates($status = true)
    {
        if (is_bool($status)) {
            $this->_selectMultipleDates = $status;
        } else {
            $this->_selectMultipleDates = true;
        }
    }

    /**
     * Selected dates
     *
     * @param   array    $dates  An array of dates, in the same format
     *                           that DateFormat (UNIX) will use
     * @access  public
     */
    function setSelectedDates($dates)
    {
        foreach ($dates as $date) {
            $this->addSelectedDate($date);
        }
        $this->selectMultipleDates();

    }

    /**
     * Add a new selected date
     *
     * @param   string   $date   Selected date
     * @access  public
     */
    function addSelectedDate($date)
    {
        list($year, $month, $day) = preg_split('/-/', $date);
        if (!isset($month) && !is_numeric($month)) {
            die('[PIWI] - Dates should be: YYYY-MM-DD');
        }
        $this->_selectedDates[] = $date;
        $this->selectMultipleDates();
    }

    /**
     * Show a time picker?
     *
     * @param   boolean  $status True or false
     * @access  public
     */
    function showTimePicker($status = true)
    {
        if (is_bool($status)) {
            $this->_showTimePicker = $status;
        } else {
            $this->_showTimePicker = true;
        }
    }

    /**
     * Adds an event
     *
     * The difference between this AddEvent and the
     * one in Bin:: is that it support jscalendar events ;-)
     */
    function addEvent($event)
    {
        if (is_string($event) && func_num_args() == 2) {
            $action = func_get_arg(1);
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                if (in_array ($event, $this->_availableEvents)) {
                    switch($event) {
                    case ON_CHANGE:
                        $this->_entry->AddEvent(ON_CHANGE, $action);
                        break;
                    case ON_UPDATE:
                        $this->_onUpdateEvent = $action;
                        break;
                    case ON_CLOSE:
                        $this->_onCloseEvent = $action;
                        break;
                    case ON_SELECT:
                        $this->_onSelectEvent = $action;
                        break;
                    }
                } else {
                    die('[PIWI] - Sorry but you are not permitted to use '.$event.' in this widget');
                }
            } else {
                $this->_Events[] = new JSEvent($event, $action);
            }
        } elseif (is_object($event) && strtolower(get_class ($event)) == 'jsevent') {
            if (is_array($this->_availableEvents) && count($this->_availableEvents) > 0) {
                if (in_array($event->getID(), $this->_availableEvents)) {
                    $id = $event->getID();
                    switch($id) {
                    case ON_UPDATE:
                        $this->_onUpdateEvent = $event->getCode();
                        break;
                    case ON_CLOSE:
                        $this->_onCloseEvent = $event->getCode();
                        break;
                    case ON_SELECT:
                        $this->_onSelectEvent = $event->getCode();
                        break;
                    }
                } else {
                    die('[PIWI] - Sorry but you are not permitted to use '.$event->getID().' in this widget');
                }
            }
        } else {
            die('[PIWI] - Events should be objects');
        }
    }

    function setIncludeCSS($withCSS)
    {
        $this->_includeCSS = $withCSS;
    }

    function setIncludeJS($withJS)
    {
        $this->_includeJS = $withJS;
    }

    function _buildXHTML()
    {
        $this->_entry->setReadOnly($this->_readOnly);
        $this->_entry->setEnabled($this->_isEnabled);
        $this->_button->setEnabled($this->_isEnabled);
        $this->_XHTML = "<table";
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= $this->buildJSEvents();
        $this->_XHTML .= ">\n";
        $this->_XHTML .= " <tr>\n";
        $this->_XHTML .= "  <td>\n";
        $this->_XHTML .= $this->_entry->get();
        $this->_XHTML .= "  </td>\n";
        $this->_XHTML .= "  <td>\n";
        $this->_XHTML .= $this->_button->get();
        $this->_XHTML .= "  </td>\n";
        $this->_XHTML .= " </tr>\n";
        $this->_XHTML .= "</table>";
    }

    /**
     * Construct the widget
     *
     * @access public
     */
    function buildXHTML()
    {
        $this->_buildXHTML();
/*
        if ($this->_includeCSS) {
            $theme = PIWI_URL . 'piwidata/js/jscalendar/calendar-' . $this->_theme . '.css';
            $this->addFile($theme);
        }

        if ($this->_includeJS) {
            //add the js file!
            if ($this->_calType != 'gregorian') {
                $this->addFile(PIWI_URL . 'piwidata/js/jscalendar/'.$this->_calType.'.js');
            }

            $this->addFile(PIWI_URL . 'piwidata/js/jscalendar/calendar.js');
            $this->addFile(PIWI_URL . 'piwidata/js/jscalendar/calendar-setup.js');
            $this->addFile($this->_langFile);
        }

        $this->_XHTML .= "<script type=\"text/javascript\">\n";
        if ($this->_selectMultipleDates) {
            $dateVar = "dateOf" . $this->_tableID . '_' . rand();
            $this->_XHTML .= "  var multipleDates_".$this->_tableID." = [];\n";

            if (count($this->_selectedDates) > 0) {
                $this->_XHTML .= "  var selectedDates_".$this->_tableID." = new Array(".count($this->_selectedDates).");\n";
                $this->_XHTML .= "var datehandler = new Date();\n";
                $i = 0;
                foreach ($this->_selectedDates as $date) {
                    list($year, $month, $day) = preg_split('/-/', $date);
                    if (isset($month)) {
                        //Damn javascript, it thinks that 04 is May and not april..
                        $month = (int)$month;
                        $month = $month-1;
                        $this->_XHTML .= "  selectedDates_".$this->_tableID."[".$i."] = new Date(".$year.",".$month.",".$day.");\n";
                        $i++;
                    }
                }
                $this->_XHTML .= "\n";
                $this->_XHTML .= "  multipleDates_".$this->_tableID." = selectedDates_".$this->_tableID.";\n";

            }
            $this->_XHTML .= "  function updateMultipleDatesIn".$this->_tableID."(calendar) {\n";
            $this->_XHTML .= "     multipleDates_".$this->_tableID.".length = 0;\n";
            $this->_XHTML .= "     for (var i in calendar.multiple) {\n";
            $this->_XHTML .= "       var ".$dateVar." = calendar.multiple[i];\n";
            $this->_XHTML .= "       if (".$dateVar.") {\n";
            $this->_XHTML .= "           multipleDates_".$this->_tableID."[multipleDates_".$this->_tableID.".length] = ".$dateVar.";\n";
            $this->_XHTML .= "       }\n";
            $this->_XHTML .= "     }\n";
            $this->_XHTML .= "     calendar.hide();\n";
            $this->_XHTML .= "     return true;\n";
            $this->_XHTML .= "   }\n";
        }
        $this->_XHTML .= " Calendar.setup({\n";
        $this->_XHTML .= "  inputField: \"".$this->_entry->getID()."\",\n";
        $this->_XHTML .= "  ifFormat: \"".$this->_dateFormat."\",\n";
        $this->_XHTML .= "  dateType: \"".$this->_calType."\",\n";
        $this->_XHTML .= "  button: \"".$this->_button->getID()."\",\n";
        $this->_XHTML .= "  singleClick: true,\n";
        if ($this->_showWeekNumbers) {
            $this->_XHTML .= "  weekNumbers: true,\n";
        } else {
            $this->_XHTML .= "  weekNumbers: false,\n";
        }

        if (!is_null($this->_firstDay)) {
            $this->_XHTML .= "  firstDay: ".$this->_firstDay.",\n";
        }

        if (!empty($this->_initDate) || !empty($this->_value)) {
            if (empty($this->_initDate)) {
                $this->_XHTML .= "  date: \"".$this->_value."\",\n";
            } else {
                $this->_XHTML .= "  date: \"".$this->_initDate."\",\n";
            }
        }

        if ($this->_showTimePicker) {
            $this->_XHTML .= "  showsTime: true,\n";
        } else {
            $this->_XHTML .= "  showsTime: false,\n";
        }

        if ($this->_selectMultipleDates) {
            $this->_XHTML .= "  onClose: updateMultipleDatesIn".$this->_tableID;
            if (!empty($this->_onCloseEvent)) {
                $this->_XHTML .= ", ".$this->onCloseEvent.",\n";
            } else {
                $this->_XHTML .= ",\n";
            }
            $this->_XHTML .= "  multiple: multipleDates_".$this->_tableID.",\n";
        } else {
            $this->_XHTML .= "  multiple: false,\n";
        }

        if (!empty($this->_onUpdateEvent)) {
            $this->_XHTML .= "  onUpdate: ".$this->_onUpdateEvent.",\n";
        }

        if (!empty($this->_onSelectEvent)) {
            $this->_XHTML .= "  onSelect: ".$this->_onSelectEvent.",\n";
        }

        if (!empty($this->_onCloseEvent) && !$this->_selectMultipleDates) {
            $this->_XHTML .= "  onClose: ".$this->_onCloseEvent."\n";
        }

        if (substr($this->_XHTML, -2) == ",\n") {
            $this->_XHTML = substr($this->_XHTML, 0, -2);
        }
        $this->_XHTML .= "});\n";
        $this->_XHTML .= "</script>\n";
*/
    }

}