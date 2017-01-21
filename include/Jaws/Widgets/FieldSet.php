<?php
require_once JAWS_PATH . 'libraries/piwi/Widget/Container/FieldSet.php';

/**
 * Overwrites the Piwi fieldset and creates one that works for Jaws
 *
 * @category   Widget
 * @package    Core
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Widgets_FieldSet extends FieldSet
{
    /**
     * Constructor
     *
     * @access  public
     * @param   string  $legend
     * @return  void
     */
    function __construct($legend = '')
    {
        parent::FieldSet($legend);
        $this->_direction = 'vertical';
    }

    /**
     * Build the XHTML data
     *
     * @access    public
     */
    function buildXHTML()
    {
        $this->_XHTML  = '<fieldset';
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= ">\n";

        if (!empty($this->_legend)) {
            $legend_id = !empty($this->_legendID) ? $this->_legendID : $this->_id.'_legend';
            $this->_XHTML .= '<legend id="' . $legend_id . '"><span>' . $this->_legend . "</span></legend>\n";
        }

        if ($this->_direction == 'horizontal') {
            $this->_XHTML .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr>\n";
        }

        $noLabel = array('staticentry', 'radiobuttons', 'checkbuttons');
        foreach ($this->_items as $item) {
            if ($this->_direction == 'horizontal') {
                $this->_XHTML .= "<td>\n";
            }
            if (method_exists($item['control'], 'rebuildJS')) {
                $item['control']->rebuildJS();
            }
            $this->addJS($item['control']->getJS());
            $this->addFiles($item['control']->getFiles());

            $title      = $item['control']->getTitle();
            $field      = $item['control']->get();
            $class      = $item['control']->getClass();
            $cont_class = $item['control']->getContainerClass();
            if ($class != '') {
                $class = ' class="' . $class . '"';
            }

            if ($cont_class != '') {
                $cont_class = ' class="' . $cont_class . '"';
            }

            $this->_XHTML .= '<div' . $cont_class . '>' . "\n";

            if (!empty($title)) {
                if (!in_array($item['control']->getClassName(), $noLabel)) {
                    $this->_XHTML .= '  <label id="'.$item['control']->getId().'_label" for="' . $item['control']->getId() . '">';
                } else {
                    $this->_XHTML .= '  <strong>';
                }

                $this->_XHTML .= $title;
                if (method_exists($item['control'], 'requiresTwoColons')) {
                    if ($item['control']->requiresTwoColons()) {
                        $this->_XHTML .= ":&nbsp;";
                    }
                }

                if (!in_array($item['control']->getClassName(), $noLabel)) {
                    $this->_XHTML .= '</label>' . "\n";
                } else {
                    $this->_XHTML .= '</strong><br />' . "\n";
                }
            }
            $this->_XHTML .= '  ' . $field;
            $this->_XHTML .= " </div><br />\n\n";
            if ($this->_direction == 'horizontal') {
                $this->_XHTML .= "</td>\n";
            }
        }

        if ($this->_direction == 'horizontal') {
            $this->_XHTML .= "</tr></table>\n";
        }

        $this->_XHTML .= "</fieldset>\n";
    }

    /**
     * Add a widget, just bin widgets!
     *
     * @access  public
     * @param   object  $widget     Widget To add
     * @param   string  $comment
     * @return  void
     */
    function add(&$widget, $comment = '')
    {
        $name = $widget->getClassName();
        if ($widget->getFamilyWidget() == 'bin' || $widget->getFamilyWidget() == 'container') {
            array_push($this->_items,  array('control' => &$widget,
                                             'comment' => $comment));
        } else {
            die("Sorry, you must add a bin widget (an entry, button, combo, etc) to a FieldSet");
        }
    }
}
