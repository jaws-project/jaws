<?php
/**
 * Extensions to the Piwi DatePicker
 *
 * @category   Widget
 * @package    Core
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Widget that interacts with piwi and jaws and extends Piwi::DatePicker
 */
require_once JAWS_PATH . 'libraries/piwi/Widget/Bin/DatePicker.php';

class Jaws_Widgets_DatePicker extends DatePicker
{
    /**
     * Default theme
     * @var string
     */
    var $_theme = 'calendar-system';

    /**
     * Set DatePicker theme
     *
     * @access  public
     * @param   string   $theme Name of theme
     * @return  void
     **/
    function setTheme($theme)
    {
        $theme = strtolower($theme);
        $themes = array(
            'blue', 'blue2', 'brown',
            'green', 'system', 'tas', 'win2k-1',
            'win2k-2', 'win2k-cold-1', 'wink2-cold-2'
        );

        if (!in_array($theme, $themes)) {
            $theme = 'calendar-system';
        }

        if (!strstr('calendar-', $theme)) {
            $theme = 'calendar-' . $theme;
        }

        $this->_theme = $theme;
    }

    /**
     * Build the XHTML
     *
     * @access  private
     * @return  void
     **/
    function _buildXHTML()
    {
        $this->_XHTML .= $this->_entry->get();
        $this->_XHTML .= $this->_button->get();
    }

    /**
     * Build the XHTML
     *
     * @access  public
     * @return  void
     **/
    function buildXHTML()
    {
        $GLOBALS['app']->Layout->AddHeadLink(
            'libraries/piwi/data/css/'. $this->_theme. '.css',
            'stylesheet',
            'text/css'
        );
        parent::buildXHTML();
    }

}