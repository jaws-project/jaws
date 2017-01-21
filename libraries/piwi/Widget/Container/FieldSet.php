<?php
/**
 * FieldSet.php - FieldSet Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('FIELDSET_REQ_PARAMS', 1);
class FieldSet extends Container
{
    /**
     * Legend of the fieldset
     *
     * @var      string    $_legend
     * @see      setLegend()
     * @access   private
     */
    var $_legend;

    /**
     * Legend ID of the fieldset
     *
     * @var      string    $_legendID
     * @see      setLegendID()
     * @access   private
     */
    var $_legendID;

    /**
     * Public constructor
     *
     * @access   public
     */
    function __construct($legend = '')
    {
        $this->_name      = 'fieldset' . rand(1, 100);
        $this->_direction = 'horizontal';
        $this->_legendID  = '';
        $this->setLegend($legend);
        parent::init();
    }

    /**
     * Set the legend
     *
     * @param    string   $legend  Legend
     * @access   public
     */
    function setLegend($legend)
    {
        $this->_legend = $legend;
    }

    /**
     * Set the legend ID
     *
     * @param    string   $id  Legend ID
     * @access   public
     */
    function setLegendID($id)
    {
        $this->_legendID = $id;
    }

    /**
     * Add a widget, just bin widgets!
     *
     * @param    object   $widget  Widget To add
     * @access   public
     */
    function add(&$widget, $comment = '')
    {
        $name = $widget->getClassName();
        if ($widget->getFamilyWidget() == 'bin' || $name == 'hbox' || $name == 'vbox') {
            array_push($this->_items,  array('control' => &$widget,
                                             'comment' => $comment));
        } else {
            die("Sorry, you must add a bin widget (an entry, button, combo, etc) to a FieldSet");
        }
    }

    /**
     * Build the piwiXML data.
     *
     * @access    public
     */
    function buildPiwiXM ()
    {
        $this->buildBasicPiwiXML();

        if (!empty($this->_legend)) {
            $this->_PiwiXML->openElement('legend');
            $this->_PiwiXML->addText($this->_legend);
            $this->_PiwiXML->closeElement('legend');
        }

        $this->_PiwiXML->addAttribute('direction', $this->_direction);
        if (count($this->_items) > 0) {
            $this->_PiwiXML->openElement('items');
            foreach ($this->_items as $item) {
                $this->_PiwiXML->addXML($item['control']->getPiwiXML(true));
            }
            $this->_PiwiXML->closeElement('items');
        }
        $this->_PiwiXML->closeElement($this->getClassName());
    }

    /**
     * Build the XHTML data
     *
     * @access    public
     */
    function buildXHTML()
    {
        $this->_XHTML = "<fieldset";
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= ">\n";

        if (!empty($this->_legend)) {
            $legend_id = !empty($this->_legendID) ? $this->_legendID : $this->_id.'_legend';
            $this->_XHTML .= '<legend id="' . $legend_id . '"><span>' . $this->_legend . "</span></legend>\n";
        }

        if ($this->_direction == 'vertical') {
            $this->_XHTML .= "<table>\n";
        }

        foreach ($this->_items as $item) {
            if (method_exists($item['control'], 'rebuildJS')) {
                $item['control']->rebuildJS();
            }
            $this->addJS ($item['control']->getJS());
            $this->addFiles($item['control']->getFiles());

            $title = $item['control']->getTitle();
            $field = $item['control']->get();
            $id = $item['control']->getID();

            if ($this->_direction == 'vertical') {
                $this->_XHTML .= "<tr>\n";
            }

            if (!empty($title)) {
                if ($this->_direction == 'vertical') {
                    $this->_XHTML .= " <td valign=\"top\" id=\"" . $id . "_title\">\n";
                    $this->_XHTML .= $title;
                    if (method_exists($item['control'], 'requiresTwoColons')) {
                        if ($item['control']->requiresTwoColons()) {
                            $this->_XHTML .= ":&nbsp;";
                        }
                    }

                    $this->_XHTML .= " </td>\n";
                    $this->_XHTML .= " <td valign=\"top\" id=\"" . $id . "_field\">\n";
                    $this->_XHTML .= $field."\n";
                    $this->_XHTML .= " </td>\n";
                    if (!empty($item['comment'])) {
                        $this->_XHTML .= "</tr>\n";
                        $this->_XHTML .= "<tr>\n";
                        $this->_XHTML .= "<td></td><td class=\"form_warning\">".$item['comment']."</td>\n";
                    }
                } else {
                    $this->_XHTML .= $title;
                    if (method_exists($item['control'], 'requiresTwoColons')) {
                        if ($item['control']->requiresTwoColons()) {
                            $this->_XHTML .= ":&nbsp;";
                        }
                    }

                    //FIXME: comments are disabled for horizontal fieldsets
                    $this->_XHTML .= $field."\n";
                }
            } else {
                if ($this->_direction == 'vertical') {
                    switch ($item['control']->getClassName()) {
                        case 'button':
                            $this->_XHTML .= "  <td colspan=\"2\" class=\"buttons\">\n";
                            $this->_XHTML .= $field;
                            $this->_XHTML .= "  </td>";
                            break;

                        default:
                            $this->_XHTML .= "<td></td><td>";
                            $this->_XHTML .= $field;
                            $this->_XHTML .= "</td>";
                            break;
                    }
                } else
                    $this->_XHTML .= $field;
            }

            if ($this->_direction == 'vertical') {
                $this->_XHTML .= "</tr>\n";
            }
        }

        if ($this->_direction == 'vertical') {
            $this->_XHTML .= "</table>\n";
        }

        $this->_XHTML .= "</fieldset>\n";
    }
}
?>
