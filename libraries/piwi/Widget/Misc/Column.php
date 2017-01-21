<?php
/**
 * Column.php - Column Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Misc/Misc.php';

define('COLUMN_REQ_PARAMS', 2);
class Column extends Misc
{
    /**
     * Url to execute
     *
     * @var      string $_url
     * @access   private
     * @see      setUrl(), getUrl()
     */
    var $_url;

    /**
     * Use an image
     *
     * @var      string $_image
     * @access   private
     * @see      setImage()
     */
    var $_image;

    /**
     * The column name
     *
     * @var      string $_columnName
     * @access   private
     * @see      setColumnName(), getColumnName()
     */
    var $_columnName;

    /**
     * Callback to use for parsing a custom data type.
     *
     * @var      callback $_callback
     * @access   private
     * @see      setCallback(), getCallback()
     */
    var $_callback;

    /**
     * Is it visible?
     *
     * @var      string $_visible
     * @access   private
     * @see      setVisible(), isVisible()
     */
    var $_visible;

    /**
     * Is it sortable?
     *
     * @var      string $_sortable
     * @access   private
     * @see      setSortable(), isSortable()
     */
    var $_sortable;

    /**
     * The column type.
     *
     * Default values: String, CaseInsensitiveString, Number, DateTime, Boolean, Data, Custom
     *
     * @var      string $_type
     * @access   private
     * @see      setType(), getType()
     */
    var $_type;

    /**
     * Header javascript, when user hits the '<th>'.
     *
     * @var      string  $_JS
     * @access   private
     * @see      setJSAction(), getJSAction()
     */
    var $_JS;

    /**
     * Public constructor
     *
     * @param    string  $title Title (label)
     * @param    string  $colname Column name that matches
     * @param    boolean $sortable Is it sortable?
     * @param    string  $type Type of the column (to sort)
     * @param    boolean $visible Is it visible?
     * @param    string  $url  Url to point
     * @param    callback $callback A function/method to format the raw value
     * @param    string  $js  JS to execute
     * @see      http://www.php.net/manual/en/function.call-user-func-array.php
     *
     * @access   public
     */
    function __construct($title, $colname, $sortable = true, $type = 'String', $visible = true, $url = '', $callback = null,
                      $js = '')
    {
        $this->_title      = $title;
        $this->_url        = $url;
        $this->_columnName = $colname;
        $this->_visible    = $visible;
        $this->_type       = $type;
        $this->_JS         = $js;

        if (!empty($js)) {
            $this->_sortable = false; //Or we sort or we execute the JS?
        } else {
            $this->_sortable   = $sortable;
        }

        if (!is_null($callback)) {
            $this->_callback = $callback;
        }

        parent::init();
    }

    /**
     * Set the ColumnName
     *
     * @access   public
     * @param    string  $colname The ColumnName
     */
    function setColumnName($colname)
    {
        $this->_columnName = $colname;
    }

    /**
     * Get the ColumnName
     *
     * @access   public
     */
    function getColumnName()
    {
        return $this->_columnName;
    }

    /**
     * Set the url
     *
     * @access   public
     * @param    string  $url The URL to point
     */
    function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * Get the url
     *
     * @access   public
     */
    function getUrl()
    {
        return $this->_url;
    }

    /**
     * Has an url?
     *
     * @access   public
     */
    function hasUrl()
    {
        return !empty($this->_url);
    }

    /**
     * Has Callback?
     *
     * @access   public
     */
    function needsCallback()
    {
        return !is_null($this->_callback);
    }

    /**
     * Set the callback
     *
     * @access   public
     * @param    string  $callback A function/method to call when parsing the data.
     */
    function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    /**
     * Get the callback
     *
     * @access   public
     */
    function getCallback()
    {
        return $this->_callback;
    }


    /**
     * Run any callbacks listed for the column.
     *
     * @access public
     */
    function parse($value)
    {
        if (isset($this->_callback)) {
            if (is_callable($this->_callback)) {
                return call_user_func_array($this->_callback, array($value));
            }
        }

        return $value;
    }

    /**
     * Set the visible status
     *
     * @access   public
     * @param    string  $status Visible Status
     */
    function setVisible($visible = true)
    {
        $this->_visible = $visible;
    }

    /**
     * Is it Visible?
     *
     * @access   public
     */
    function isVisible()
    {
        return $this->_visible;
    }

    /**
     * Set the sortable status
     *
     * @access   public
     * @param    string  $status Sortable Status
     */
    function setSortable($sortable)
    {
        $this->_sortable = $sortable;
    }

    /**
     * Is it Sortable?
     *
     * @access   public
     */
    function isSortable()
    {
        return $this->_sortable;
    }

    /**
     * Set the type
     *
     * @access   public
     * @param    string  $type The type of the column
     */
    function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Get the type
     *
     * @access   public
     */
    function getType()
    {
        return $this->_type;
    }

    /**
     * Set the JS action on <th>
     *
     * @access   public
     * @param    string  $action  JS action
     */
    function setJSAction($action)
    {
        $this->_JS = $action;
        $this->_sortable = false;
    }

    /**
     * Get the JS Action
     *
     * @access   public
     */
    function getJSAction()
    {
        return $this->_JS;
    }
}
?>