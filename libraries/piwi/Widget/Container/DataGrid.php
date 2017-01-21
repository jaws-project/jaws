<?php
/**
 * DataGrid Class
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Container
 * @package    DataGrid
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2006 Piwi
 * @license    http://www.gnu.org/copyleft/lesser.html
 *
 *
 * The conf variables of Datagrid are:
 *   - DATAGRID_CLASS_CSS:         for the className
 *   - DATAGRID_ACTION_LABEL:      for actionLabel
 *   - DATAGRID_DATA_ONLOADING:    JS action to execute when datagrid is reading data (array)
 *   - DATAGRID_DATA_ONLOADED:     JS action to execute once data has been readed
 *   - DATAGRID_PAGER_LABEL_FIRST: for the 'first' string
 *   - DATAGRID_PAGER_LABEL_PREV:  for the 'previous' string
 *   - DATAGRID_PAGER_LABEL_NEXT:  for the 'next' string
 *   - DATAGRID_PAGER_LABEL_LAST:  for the 'last' string
 *   - DATAGRID_PAGER_MODE:        for the pager mode (COMBO, NORMAL, COMBO_COMPLETE, NORMAL_COMPLETE)
 *   - DATAGRID_PAGER_FIRSTACTION: for the first Action link (or JS command)
 *   - DATAGRID_PAGER_PREVACTION:  for the previous Action link (or JS command)
 *   - DATAGRID_PAGER_NEXTACTION:  for the next Action link (or JS command)
 *   - DATAGRID_PAGER_LASTACTION:  for the last Action link (or JS command)
 *   - DATAGRID_PAGER_GOTOACTION:  for the goto Action link (or JS command)
 *   - DATAGRID_PAGER_PAGEBY:      for the pager size
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('DATAGRID_REQ_PARAMS', 1);

if (!defined('PIWI_PAGER_COMBO')) {
    define('PIWI_PAGER_COMBO', 'COMBO');
}

if (!defined('PIWI_PAGER_NORMAL')) {
    define('PIWI_PAGER_NORMAL', 'NORMAL');
}

class DataGrid extends Container
{
    /**
     * Columns of the datagrid
     *
     * @var    array   $_columns
     * @see    addColumn()
     * @access private
     */
    var $_columns;

    /**
     * Action Columns of the datagrid
     *
     * @var    array   $_actionColumns
     * @see    addActionColumn()
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
    var $_colors = array('even' => 'white', 'odd' => '#edf3fe');

    /**
     * Datagrid data
     *
     * @var     array   $_Data
     * @see     addData()
     * @access  private
     */
    var $_data;

    /**
     * The datagrid caption: <caption> (title of the Grid)
     *
     * @var     array   $_caption
     * @see     setCaption()
     * @access  private
     */
    var $_caption;

    /**
     * The action's label
     *
     * @var     array   $_ActionLabel
     * @see     setActionLabel()
     * @access  private
     */
    var $_actionLabel;

    /**
     * Actions column style
     *
     * @var     string  $_actionStyle
     * @see     setActionStyle()
     * @access  private
     */
    var $_actionStyle;

    /**
     * Total size of rows to be used
     * 
     * @access  private
     * @var     int
     */
    var $_rows;

    /**
     * Pager size
     *
     * @access  private
     * @var     int
     */
    var $_pagerSize;

    /**
     * Actions used by the pager
     *
     * @access  private
     * @var     array
     */
    var $_pagerAction = array();
    
    /**
     * Pager mode
     *
     * @access  private
     * @var     mixed    (a string or boolean)
     */
    var $_pager = false;

    /**
     * Pager labels array
     *
     * @access  private
     * @var     array
     */
    var $_pagerLabels = array();
    
    /**
     * Use multiple selection (like gmail and many other email web clients)
     *
     * @access  private
     * @var     boolean
     */
    var $_multipleSelection = false;

    /**
     * Actions used in the JS for onLoading and onLoaded data
     *
     * @access  private
     * @var     array 
     */
    var $_dataActions = array();
    
    /**
     * Public constructor
     *
     * @param   array   $data  Datagrid Data
     * @param   string  $caption Caption of the DataGrid
     * @param   string  $id    Id of the DataGrid
     * @access  public
     */
    function __construct($data, $caption = '', $id = '')
    {
        $this->_columns       = array();
        $this->_caption       = $caption;
        $this->_actionColumns = array();
        $this->_data          = array();

        $this->_pagerLabels   = array(
                                      'first' => 'First',
                                      'prev'  => 'Previous',
                                      'next'  => 'Next',
                                      'last'  => 'Last',
                                      );

        $this->_class = Piwi::getVarConf('DATAGRID_CLASS_CSS');
        if (empty($this->_class)) {
            $this->_class = 'datagrid';
        }

        $this->_actionLabel = Piwi::getVarConf('DATAGRID_ACTION_LABEL');
        if (empty($this->_actionLabel)) {
            $this->_actionLabel = 'Actions';
        }

        $this->_actionStyle   = '';
        $this->_pagerSize     = Piwi::getVarConf('DATAGRID_PAGER_PAGEBY');
        if (empty($this->_pagerSize)) {
            $this->_pagerSize = 10;
        }

        if (empty($id)) {
            $this->_id = 'datagrid_' . rand(1,100);
        } else {
            $this->_id = $id;
        }

        if (is_array($data)) {
            $this->_data = $data;
        } else {
            die("The data provided to ".$this->getId()." is not an array.");
        }

        $oddColor = Piwi::getVarConf('COLOR_ODD');
        if (empty($oddColor)) {
            $oddColor = '#edf3fe';
        }
        $this->setOddColor($oddColor);

        $evenColor = Piwi::getVarConf('COLOR_EVEN');
        if (empty($evenColor)) {
            $evenColor = '#fff';
        }
        $this->setEvenColor($evenColor);
        $this->addData($data);

        $pagerMode        = Piwi::getVarConf('DATAGRID_PAGER_MODE');
        $pagerFirstAction = Piwi::getVarConf('DATAGRID_PAGER_FIRSTACTION');
        $pagerPrevAction  = Piwi::getVarConf('DATAGRID_PAGER_PREVACTION');
        $pagerNextAction  = Piwi::getVarConf('DATAGRID_PAGER_NEXTACTION');
        $pagerLastAction  = Piwi::getVarConf('DATAGRID_PAGER_LASTACTION');
        $pagerGotoAction  = Piwi::getVarConf('DATAGRID_PAGER_GOTOACTION');

        if (!empty($pagerMode)) {
            if (defined($pagerMode)) {
                $pagerMode = constant($pagerMode);
            } 
            $this->usePager($pagerMode);
        }

        $this->setAction('first', $pagerFirstAction);
        $this->setAction('prev',  $pagerPrevAction);
        $this->setAction('next',  $pagerNextAction);
        $this->setAction('last',  $pagerLastAction);
        $this->setAction('goto',  $pagerGotoAction);

        $firstLabel = Piwi::getVarConf('DATAGRID_PAGER_LABEL_FIRST');
        if (!empty($firstLabel)) {
            $this->setLabelString('first', $firstLabel);
        }

        $prevLabel = Piwi::getVarConf('DATAGRID_PAGER_LABEL_PREV');
        if (!empty($prevLabel)) {
            $this->setLabelString('prev', $prevLabel);
        }

        $nextLabel = Piwi::getVarConf('DATAGRID_PAGER_LABEL_NEXT');
        if (!empty($nextLabel)) {
            $this->setLabelString('next', $nextLabel);
        }
        
        $lastLabel = Piwi::getVarConf('DATAGRID_PAGER_LABEL_LAST');
        if (!empty($lastLabel)) {
            $this->setLabelString('last', $lastLabel);
        }

        $this->_dataActions['onLoading'] = Piwi::getVarConf('DATAGRID_DATA_ONLOADING');
        $this->_dataActions['onLoaded']  = Piwi::getVarConf('DATAGRID_DATA_ONLOADED');

        parent::init();
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
     * Set the action for onLoadingData (used on the JS interface)
     *
     * @access  public
     * @param   string  $action JS method
     */
    function onLoadingData($action)
    {
        $this->_dataActions['onLoading'] = $action;
    }

    /**
     * Set the action for onLoadedData (used on the JS interface)
     *
     * @access  public
     * @param   string  $action JS method
     */
    function onLoadedData($action)
    {
        $this->_dataActions['onLoaded'] = $action;
    }
    
    /**
     * Set the style of the actions column
     *
     * @param  string  $style  Style
     * @access public
     */
    function setActionStyle($style)
    {
        $this->_actionStyle = $style;
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
     * Set the label of the actions column
     *
     * @param  string  $label  Label
     * @access public
     */
    function setActionLabel($label)
    {
        $this->_actionLabel = $label;
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
     * Use multiple selection?
     *
     * @access  public
     * @param   boolean $use  True/False
     */
    function useMultipleSelection($use = true)
    {
        $this->_multipleSelection = $use;
    }

    /**
     * Sets labels (strings) of DataGrid
     *
     * @access  public
     * @param   string  $label
     * @param   string  $value  Translated string
     */
    function setLabelString($label, $value)
    {
        $this->_pagerLabels[$label] = $value;
    }

    /**
     * Sets the pager mode to use:
     *
     *   - PIWI_PAGER_COMBO: Uses a combo and the option values will be: 1-10, 11-20...
     *   - PIWI_PAGER_NORMAL: Uses a normal pager, with just two arrows: << (previous) and >> (next)
     *   - PIWI_PAGER_COMBO_COMPLETE: The same as the COMBO one, but it also adds arrows
     *   - PIWI_PAGER_NORMAL_COMPLETE: Same as NORMAL, but it also adds the number of pages
     */
    function usePager($pager = PIWI_PAGER_NORMAL)
    {
        $this->_pager = $pager;
    }

    /**
     * Sets the actions of DataGrid
     *
     * Sets the action type as 'goto' when user clicks on a group of pages (0-10, 11-20, etc), only works if pager is enabled and combo
     *
     * $action can be an url, which should be something like:
     *        echo $nextLabel;
     *   $action = 'url: foo.php?bar=nextBar';
     *
     * Or can be a javascript:
     *
     *   $action = 'javascript: nextBar();
     *
     * If no url: or javascript: is found, url: will be used (window.location)
     *
     * @access  public
     * @param   string  $action Action to be used
     */
    function setAction($type, $action)
    {
        if (empty($action)) {
            $this->_pagerAction[$type] = 'javascript:void(0)';
        } elseif (strpos($action, 'url:')) {
            $action = str_replace('url:', '', $action);
            $action = trim($action);
            $this->_pagerAction[$type] = "javascript: window.location = '". $action."';";            
        } else {
            if (strpos($action, 'javascript') >= 0) {
                $this->_pagerAction[$type] = $action;
            } else {
                $action = trim($action);
                $this->_pagerAction[$type] = "javascript: window.location = '". $action."';";
            }
        }
    }

    /**
     * Sets the action when user clicks on >> next (if pager is enabled)
     *
     * $action can be an url, which should be something like:
     *        echo $nextLabel;

     *   $action = 'url: foo.php?bar=nextBar';
     *
     * Or can be a javascript:
     *
     *   $action = 'javascript: nextBar();
     *
     * If no url: or javascript: is found, url: will be used (window.location)
     *
     * @access  public
     * @param   string  $action Action to be used
     */
    function setNextAction($action)
    {
        if (strpos($action, 'url:')) {
            $action = str_replace('url:', '', $action);
            $action = trim($action);
            $this->_pagerAction['next'] = "javascript: window.location = '". $action."';";            
        } else {
            if (strpos($action, 'javascript') >= 0) {
                $this->_pagerAction['next'] = $action;
            } else {
                $action = trim($action);
                $this->_pagerAction['next'] = "javascript: window.location = '". $action."';";
            }
        }
    }

    /**
     * Sets the pager size.
     *
     * For example, if user sets 10 as the pager size and he has 30 results, the pager will
     * divide the results as:
     *
     *   * 1-10 (value=1-10)
     *   * 11-20 (value=11-20)
     *   * 21-30 (value=21-30)
     *
     * @access  public
     * @param   int     $size  Pager size
     */
    function pageBy($size)
    {
        $this->_pagerSize = $size;
    }
    
    /**
     * Receives the total number of rows to be inserted, if user doesn't pass any value
     * the value will be the same as: count($data)
     *
     * @access  public
     * @param   int     $rows  Number of rows
     */
    function totalRows($rows)
    {      
        $this->_rows = $rows;
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
     * Builds the pager
     *
     * @access  private
     * @return  string   XHTML of the pager
     */
    function buildPager($class_name)
    {
        $xhtml = '';
        switch($this->_pager) {
        case PIWI_PAGER_NORMAL:
            $xhtml.= "<table id=\"pagerTableStatusOf_".$this->_id."\" class=\"". $class_name."Pager \" style=\"";
            if (isset($this->_rows)) {
                if ($this->_rows > $this->_pagerSize) {
                    $xhtml.= "display: table;";
                } else {
                    $xhtml.= "display: none;";
                }
            } else {
                $xhtml.= "display: none;";
            }
            $xhtml.= "\">\n";
            $xhtml.= " <tr>\n";
            $xhtml.= "  <td>";

            //first
            if (strpos($this->_pagerAction['first'], 'javascript') === false) {
                $xhtml.= "<a id=\"".$this->_id."_pagerFirstAnchor\" href=\"".
                    $this->_pagerAction['first']."\">".$this->_pagerLabels['first']."</a>&nbsp;";
            } else {
                $xhtml.= "<a id=\"".$this->_id."_pagerFirstAnchor\" href=\"javascript:void(0)\" onclick=\"".
                    $this->_pagerAction['first']."\">".$this->_pagerLabels['first']."</a>&nbsp;";
            }

            //previous
            if (strpos($this->_pagerAction['prev'], 'javascript') === false) {
                $xhtml.= "<a id=\"".$this->_id."_pagerPreviousAnchor\" href=\"".
                    $this->_pagerAction['prev']."\">".$this->_pagerLabels['prev']."</a>&nbsp;";
            } else {
                $xhtml.= "<a id=\"".$this->_id."_pagerPreviousAnchor\" href=\"javascript:void(0)\" onclick=\"".
                    $this->_pagerAction['prev']."\">".$this->_pagerLabels['prev']."</a>&nbsp;";
            }

            if (isset($this->_rows)) {
                if ($this->_rows > $this->_pagerSize) {
                    $xhtml.= '<span id="pagerStatusOf_'.$this->_id.'"> 1  - '.$this->_pagerSize .' ('. $this->_rows .') </span>';
                } else {
                    $xhtml.= '<span id="pagerStatusOf_'.$this->_id.'"></span>';
                }
                $xhtml.= '&nbsp;';
            } else {
                $xhtml.= '<span id="pagerStatusOf_'.$this->_id.'">&nbsp;</span>';
                $xhtml.= '&nbsp;';
            }
            
            //next
            if (strpos($this->_pagerAction['next'], 'javascript') === false) {
                $xhtml.= "<a id=\"".$this->_id."_pagerNextAnchor\" href=\"".
                    $this->_pagerAction['next']."\">".$this->_pagerLabels['next']."</a>&nbsp;";
            } else {
                $xhtml.= "<a id=\"".$this->_id."_pagerNextAnchor\" href=\"javascript:void(0)\" onclick=\"".
                    $this->_pagerAction['next']."\">".$this->_pagerLabels['next']."</a>&nbsp;";
            }

            //last
            if (strpos($this->_pagerAction['last'], 'javascript') === false) {
                $xhtml.= "<a id=\"".$this->_id."_pagerLastAnchor\" href=\"".
                    $this->_pagerAction['last']."\">".$this->_pagerLabels['last']."</a>&nbsp;";
            } else {
                $xhtml.= "<a id=\"".$this->_id."_pagerLastAnchor\" href=\"javascript:void(0)\" onclick=\"".
                    $this->_pagerAction['last']."\">".$this->_pagerLabels['last']."</a>&nbsp;";
            }

            $xhtml.= "  </td>\n";
            $xhtml.= " </tr>\n";
            $xhtml.= "</table>\n";
            break;
        }
        return $xhtml;
    }

    /**
     * Build the XHTML data
     *
     * @access  public
     */
    function buildXHTML()
    {
        //add the js file!
        $this->addFile(PIWI_URL . 'piwidata/js/piwigrid.js');
        $this->addFile(PIWI_URL . 'piwidata/js/sorttable.js');

        $this->_XHTML = '';
        if ($this->_pager !== false) {
            $this->_XHTML = $this->buildPager($this->_class);          
        }

        $this->_XHTML .= "<table";
        $this->_XHTML .= $this->buildBasicXHTML();
        $this->_XHTML .= ">\n";

        if (!empty($this->_caption)) {
            $this->_XHTML .= "<caption>".$this->_caption."</caption>\n";
        }
        
        $js = "PiwiGrid.init(document.getElementById(\"".$this->_id."\"), document.getElementById(\"body_".$this->_id."\"));\n";
        $js.= "PiwiGrid.evenColor(document.getElementById(\"".$this->_id."\"), '".$this->_colors['even']."');\n";
        $js.= "PiwiGrid.oddColor(document.getElementById(\"".$this->_id."\"), '".$this->_colors['odd']."');\n";
        $js.= "PiwiGrid.pageBy(document.getElementById(\"".$this->_id."\"), '".$this->_pagerSize."');\n";
        if ($this->_pager !== false) {         
            $js.= "PiwiGrid.usePager(document.getElementById(\"".$this->_id."\"), true);\n";
            $js.= "PiwiGrid.pagerMode(document.getElementById(\"".$this->_id."\"), '".$this->_pager."');\n";
        } else {
            $js.= "PiwiGrid.usePager(document.getElementById(\"".$this->_id."\"), false);\n";
        }

        if (isset($this->_rows)) {
            $js.= "PiwiGrid.rowsSize(document.getElementById(\"".$this->_id."\"), ".$this->_rows.");\n";
        }

        $this->_XHTML .= "   <thead>\n";
        $this->_XHTML .= "    <tr>\n";

        if ($this->_multipleSelection) {
            $js.= "PiwiGrid.useMultipleSelection(document.getElementById(\"".$this->_id."\"), true);\n";
            $this->_XHTML .= "     <td style=\"width: 1px; text-align: center;\" ";
            $this->_XHTML .= "onclick=\"PiwiGrid.multiSelect(document.getElementById('".$this->_id."')); return false;\">";
            $this->_XHTML .= "&radic;";
            $this->_XHTML .= "</td>\n";
        } else {
            $js.= "PiwiGrid.useMultipleSelection(document.getElementById(\"".$this->_id."\"), false);\n";
        }

        $columnCounter = 0;
        foreach ($this->_columns as $column) {
            $columnJSAction = $column->getJSAction();
            if ($column->isVisible()) {
                $this->_XHTML .= "     <td ";
                if ($column->isSortable()) {
                    $this->_XHTML .= " onclick=\"ts_resortTable(this, '" . $columnCounter . "'); return false;\"";
                }
                if (!empty($column->_style)) {
                    $this->_XHTML .= ' style="' . $column->_style . '"';
                }
                $this->_XHTML .= ">";
                $this->_XHTML .= $column->getTitle();
                $this->_XHTML .= "</td>\n";
            }
            $columnCounter;
        }

        if (count($this->_actionColumns) > 0) {
            $this->_XHTML .= "     <td>";
            $this->_XHTML .= $this->_actionLabel;
            $this->_XHTML .= "</td>\n";
        }
        $this->_XHTML .= "    </tr>\n";
        $this->_XHTML .= "   </thead>\n";

        if (count($this->_data) > 0) {
            $this->_XHTML .= "   <tbody id=\"body_".$this->_id."\">\n";
            $js.= "PiwiGrid.rowsSize(document.getElementById(\"".$this->_id."\"), '".count($this->_data)."');\n";

            $color = $this->_colors['even'];
            $colorcounter = 0;
            foreach ($this->_data as $data) {
                $this->_XHTML .= "    <tr valign=\"top\" style=\"background-color: ".$color.";\">\n";

                foreach ($this->_columns as $column) {
                    if ($column->isVisible()) {
                        $this->_XHTML .= '     <td';
                        if (!empty($column->_style)) {
                            $this->_XHTML .= ' style="' . $column->_style . '"';
                        }
                        $this->_XHTML .= '>';
                        $col_name = $column->getColumnName();
                        if ($column->isVisible ()) {
                            if (array_key_exists($col_name, $data)) {
                                if ($column->hasUrl()) {
                                    $url = $column->getUrl ();
                                    if (preg_match_all("#\{(.*?)\}#s", $url, $matches)) {
                                        $count = count($matches[1]);
                                        for ($j = 0; $j < $count; $j++) {
                                            $url = str_replace($matches[0][$j],
                                                               $data[$matches[1][$j]],
                                                               $url);
                                        }
                                    }
                                    //$url = urlencode ($url);
                                    $this->_XHTML .= "<a href=\"".$url."\">".$data[$col_name]."</a>";
                                } else {
                                    $this->_XHTML .= $column->parse($data[$col_name]);
                                }
                            } else {
                                $this->_XHTML .= '&nbsp;';
                            }
                        }
                    }
                    $this->_XHTML .= "</td>\n";
                }


                if (count ($this->_actionColumns) > 0) {
                    $this->_XHTML .= "     <td ";
                    //FIXME: When someone uses the ActionStyle it breaks the table design
                    //if (!empty ($this->_ActionStyle))
                    //$this->_XHTML .= "style=\"".$this->_ActionStyle."\"";
                    $this->_XHTML .= " style=\"white-space: nowrap;\"";
                    $this->_XHTML .= ">";
                }

                $howManyActions = count($this->_actionColumns);
                $counter = 0;
                foreach ($this->_actionColumns as $column) {
                    $counter++;
                    $url = $column->getUrl();

                    if (!$column->needsCallback()) {
                        if (preg_match_all("#\{(.*?)\}#s", $url, $matches)) {
                            $count = count($matches[1]);
                            for ($i = 0; $i < $count; $i++)
                                $url = str_replace($matches[0][$i], $data[$matches[1][$i]], $url);
                        }
                        if (strpos($url, 'javascript:') === false) {
                            $this->_XHTML .= "<a href=\"".$url."\">";
                        } else {
                            $this->_XHTML .= "<a href=\"#\" onclick=\"".$url."\">";
                        }

                        if ($column->needsImage()) {
                            $title = $column->getTitle();
                            $this->_XHTML.= '<img title ="' . $title . '" width="16" height="16" alt="' . $title . '" border="0" src="'.
                                $column->getImage().'" />';
                        } else {
                            $this->_XHTML .= $column->getTitle();
                        }

                        $this->_XHTML .= '</a>';

                        if ($counter < $howManyActions) {
                            $this->_XHTML .= '&nbsp;|&nbsp;';
                        }
                    } else {
                        $parsedcolumn = $column->parse($data[$column->getColumnName()]);
                        if (!empty ($parsedcolumn)) {
                            $this->_XHTML .= $parsedcolumn;
                            if ($counter < $howManyActions) {
                                $this->_XHTML .= "&nbsp;|&nbsp;";
                            }
                        }
                    }
                }

                if (count($this->_actionColumns) > 0) {
                    $this->_XHTML .= "</td>\n";
                }

                $this->_XHTML .= "    </tr>\n";

                if ($colorcounter % 2 == 0) {
                    $color = $this->_colors['odd'];
                } else {
                    $color = $this->_colors['even'];
                }
                $colorcounter++;
            }
            $this->_XHTML .= "   </tbody>\n";
        } else {
            $this->_XHTML .= "   <tbody id=\"body_".$this->_id."\">\n";
            $this->_XHTML .= "   <tr><td colspan=\"". (count($this->_columns)+ (int)$this->_multipleSelection)."\" style=\"display: none;\"></td></tr>\n";
            $this->_XHTML .= "   </tbody>\n";
        }
        
        $this->_XHTML .= "</table>\n";
        $this->_XHTML .= "<script type=\"text/javascript\">\n";
        $this->_XHTML .= " ts_makeSortable(document.getElementById('".$this->_id."'));\n";
        if (!empty($this->_dataActions['onLoading'])) {
            $js.= "document.getElementById('".$this->_id."').onLoadingData = ".$this->_dataActions['onLoading']."\n";
        }

        if (!empty($this->_dataActions['onLoaded'])) {
            $js.= "document.getElementById('".$this->_id."').onLoadedData = ".$this->_dataActions['onLoaded']."\n";
        }
        
        $this->_XHTML .= $js;
        $this->_XHTML .= "</script>\n";
    }
}