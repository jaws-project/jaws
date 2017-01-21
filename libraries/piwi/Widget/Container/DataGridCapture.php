<?php
/**
 * DataGridCapture.php - DataGridCapture Class
 * provides a hidden form for capturing a grid with a desired number of rows and columns to capture
 * the functions to hide/unhide and the submit options for the data.
 * @version  $Id $
 * @author   Ivan Chavero <imcsk8@gluch.org.mx>
 *
 * <c> Ivan Chavero 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('DATAGRIDCAPTURE_REQ_PARAMS', 6);
class DataGridCapture extends Container
{
    /**
     * Columns of the datagridcapture
     *
     * @var    array   $_columns
     * @see    addColumn
     * @access private
     */
    var $_columns;

    /**
     * Action Columns of the datagridcapture
     *
     * @var    array   $_actionColumns
     * @see    addActionColumn
     * @access private
     */
    var $_actionColumns;

    /**
     * Odd/Even row color
     *
     * @var     array  $_colors
     * @see     setOddColor(), setEvenColor()
     * @access  private
     */
    var $_colors = array('even' => 'white', 'odd' => 'gray');

    /**
     * Number of rows
     *
     * @var     array
     * @access  private
     */
    var $_numrows;

    /**
     * The datagridcapture caption: <caption> (title of the Grid)
     *
     * @var     array   $_caption
     * @see     setCaption
     * @access  private
     */
    var $_caption;

    /*
     * Public constructor
     *
     * @param   array   $data  Datagrid Data
     * @param   string  $caption Caption of the DataGridCapture
     * @param   string  $id    Id of the DataGridCapture
     * @access  public
     */
    function __construct($numrows, $numcolumns, $titles, $gadget, $action, $entry_type, $caption = '', $id = '')
    {
        $this->_name          = 'datagridcapture';
        $this->_class         = 'datagrid';
        $this->_columns       = array();
        $this->_caption       = $caption;
        $this->_actionColumns = array();
        $this->_data          = array();

        if (empty($id)) {
            $this->_id = 'datagridcapture_' . rand(1,100);
        } else {
            $this->_id = $id;
        }

        /*if (is_array($data)) {
            $this->_data = $data;
        } else {
            die("beh!");
        }
        */
        $this->setOddColor('#eee');
        $this->setEvenColor('#fff');

        for ($id = 0; $id < $numrows; $id++){
            $idtext = $this->_id . '_' . $id;
            $keyField1 = new Entry('name_' . $idtext, str_replace('"', '&quot;', ''));
            #$keyField->setStyle('background: transparent;border-style: solid; border: 10px;');
            $keyField1->setStyle('border-style: solid; border: 10px;');
            $keyField1->setEnabled(true);
            $keyField1->setSize(30);
            $data[$id]['key'] = $keyField1->get();

            $keyField2 = new Entry('value_' . $idtext, str_replace('"', '&quot;', ''));
            $keyField2->setStyle('background: transparent; border: 1px;');
            $keyField2->setEnabled(true);
            $keyField2->setSize(30);
            $data[$id]['value'] = $keyField2->get();
            $data[$id]['action'] = '<a OnClick="javascript: if(confirm(\'Are this values correct?\')) { window.location=\'?gadget='.
                        $gadget.'&action='.$action.'&id='.$id.'&key=\'+document.getElementById(\''.$keyField1->getID().
                        '\').value+\'&value=\'+document.getElementById(\''.$keyField2->getID().'\').value+\'&entry_type='.
                        $entry_type.'\';}"><img id="imgcat'.$id.'" src="images/stock/save.png" border="0" alt="" /></a>';

        }
        parent::init();

        foreach ($titles as $t) {
            $this->addColumn (new Column (_("$t"), "$t", true));
        }
        $this->addData($data);
    }

    /**
     * Add Data
     *
     * @param  array  $data The Datagrid data
     * @access public
     */
    function addData($data)
    {
        if (is_array($data)) {
            $this->_data = $data;
        }
    }

    /**
     * Set the caption
     *
     * @param  string  $caption  Caption
     * @access public
     */
    function setCaption($caption)
    {
        $this->_caption = $caption;
    }

    /**
     * Set the odd color
     *
     * @param  string  $color  Color
     * @access public
     */
    function setOddColor($color)
    {
        $this->_colors['odd'] = $color;
    }

    /**
     * Set the even color
     *
     * @param  string  $color  Color
     * @access public
     */
    function setEvenColor($color)
    {
        $this->_colors['even'] = $color;
    }

    /**
     * Add a column
     *
     * @param  object  $column Column's Object
     * @access public
     */
    function addColumn(&$column)
    {
        $class = $column->getClassName();
        if ($class == 'column') {
            $this->_columns[] =& $column;
            //array_push($this->_columns, &$column);
        }

        //ok.. let the users also add action columns here..
        if ($class == 'actioncolumn') {
            $this->_actionColumns[] =& $column;
        }
    }

    /**
     * Add an action column
     *
     * @param  object  $column Action Column's Object
     * @access public
     */
    function addActionColumn(&$column)
    {
        if ($column->getClassName() == 'actioncolumn') {
            $this->_actionColumns[] =& $column;
        }
    }

    /**
     * Build the Sort Color JS Function
     *
     * @access  private
     */
    function buildColorJS()
    {
        $js = $this->_id."jsobject.onsort = function () {\n";
        $js.= " var rows = ".$this->_id."jsobject.tBody.rows;\n";
        $js.= " var l = rows.length;\n";
        $js.= "   for (var i = 0; i < l; i++) {\n";
        $js.= "     if (i % 2 == 0)\n";
        $js.= "      rows[i].style.backgroundColor = \"".$this->_colors['even']."\";\n";
        $js.= "     else\n";
        $js.= "      rows[i].style.backgroundColor = \"".$this->_colors['odd']."\";\n";
        $js.= "   }\n";
        $js.= "}\n";

        return $js;

    }

    /**
     * Build the XHTML data
     *
     * @access  public
     */
    function buildXHTML ()
    {
        //add the js file!
        $this->addFile(PIWI_URL . 'piwidata/js/sortabletable.js');

        $this->_XHTML .= "\n<a onclick='javascript:if(document.getElementById(\"div_".$this->_id.
                 "\").style.visibility == \"hidden\"){document.getElementById(\"div_".
                 $this->_id."\").style.visibility=\"visible\";}else{document.getElementById(\"div_".
                 $this->_id."\").style.visibility=\"hidden\";}".
                 "'><img src=\"/images/stock/new.png\" border=0></a>";
        $this->_XHTML .= "\n<div id=\"div_$this->_id\" style=\"visibility:hidden;\">\n";

        $this->_XHTML.= "<table";
        $this->_XHTML.= $this->buildBasicXHTML();

        $this->_XHTML.= ">\n";
        if (!empty($this->_caption)) {
            $this->_XHTML.= "<caption>".$this->_caption."</caption>\n";
        }

        $js = "var ".$this->_id."jsobject = new SortableTable (document.getElementById(\"".$this->_id."\"),\n";
        $js.= "[";
        $this->_XHTML.= "   <thead>\n";
        $this->_XHTML.= "    <tr>\n";
        foreach ($this->_columns as $column) {
            if ($column->isVisible()) {
                $this->_XHTML.= "     <td style=\"text-align: center\">";
                $this->_XHTML.= $column->getTitle();
                $this->_XHTML.= "</td>\n";

                if ($column->isSortable()) {
                    $js.= "\"".$column->getType()."\",";
                } else {
                    $js.= "\"None\",";
                }
            }
        }

        if (count ($this->_actionColumns) > 0) {
            $this->_XHTML.= "     <td style=\"text-align: center\">";
            $this->_XHTML.= PiwiTranslate::translate('Actions');
            $this->_XHTML.= "</td>\n";
            $js.= "\"None\",";
        }

        //delete last comma
        $js = substr ($js, 0, -1);
        $js.= "]);\n\n\n";

        $this->_XHTML.= "    </tr>\n";
        $this->_XHTML.= "   </thead>\n";
        $this->_XHTML.= "   <tbody>\n";

        $color = $this->_colors['even'];
        $colorcounter = 0;
        foreach ($this->_data as $data) {
            $this->_XHTML.= "    <tr valign=\"top\" style=\"background-color: ".$color.";\">\n";

            foreach ($this->_columns as $column) {
                if ($column->isVisible()) {
                    $this->_XHTML .= '     <td';
                    if (!empty($column->_style)) {
                        $this->_XHTML .= ' style="' . $column->_style . '"';
                    }
                    $this->_XHTML .= '>';
                    $col_name = preg_replace("/\{(\w+)\}/e", "\$1", $column->getColumnName());
                    if ($column->isVisible()) {
                        if (array_key_exists($col_name, $data)) {
                            if ($column->hasUrl()) {
                                $url = $column->getUrl();
                                if (preg_match_all("#\{(.*?)\}#s", $url, $matches)) {
                                    $count = count($matches[1]);
                                    for ($j = 0; $j < $count; $j++) {
                                        $url = str_replace($matches[0][$j],
                                                           $d[$matches[1][$j]],
                                                           $url);
                                    }
                                }
                                $url = urlencode($url);
                                $this->_XHTML.= "<a href=\"".$url."\">".$data[$col_name]."</a>";
                            } else {
                                $this->_XHTML.= $column->parse($data[$col_name]);
                            }
                        } else {
                            $this->_XHTML .= '&nbsp;';
                        }
                    }
                }
                $this->_XHTML.= "</td>\n";
            }

            $this->_XHTML.= "     <td>";

            $howManyActions = count($this->_actionColumns);
            $counter = 0;
            foreach ($this->_actionColumns as $column) {
                $counter++;
                $url = $column->getUrl();

                if (preg_match_all("#\{(.*?)\}#s", $url, $matches)) {
                    $count = count($matches[1]);
                    for ($i = 0; $i < $count; $i++) {
                        $url = str_replace ($matches[0][$i], $data[$matches[1][$i]], $url);
                    }
                }
                //              $url = urlencode($url);

                $this->_XHTML.= "<a href=\"".$url."\">";

                if ($column->needsImage()) {
                    $title = $column->getTitle();
                    $this->_XHTML.= '<img title ="' . $title . '" alt="' . $title . '" border="0" src="'.$column->getImage().'" />';
                } else {
                    $this->_XHTML.= $column->getTitle();
                }

                $this->_XHTML.= '</a>';

                if ($counter < $howManyActions) {
                    $this->_XHTML.= '&nbsp;|&nbsp;';
                }
            }
            $this->_XHTML.= "</td>\n";
            $this->_XHTML.= "    </tr>\n";

            if ($colorcounter % 2 == 0) {
                $color = $this->_colors['odd'];
            } else {
                $color = $this->_colors['even'];
            }

            $colorcounter++;
        }
        $this->_XHTML.= "   </tbody>\n";
        $this->_XHTML.= "</table>\n";
        $this->_XHTML.= "<script type=\"text/javascript\">\n";
        $this->_XHTML.= $js;
        $this->_XHTML.= $this->buildColorJS();
        $this->_XHTML.= "</script>\n";
        $this->_XHTML.= "</div> <!-- end data capture -->";
    }
}
?>