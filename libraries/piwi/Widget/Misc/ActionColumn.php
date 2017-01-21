<?php
/**
 * ActionColumn.php - ActionColumn Class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Misc/Misc.php';

define('ACTIONCOLUMN_REQ_PARAMS', 2);
class ActionColumn extends Misc
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
     * Public constructor
     *
     * @param    string  $title Title to use
     * @param    string  $url  Url to point
     * @param    string  $img  Img to use
     * @param    string  $colname Column name that matches
     * @param    callback $callback A function/method to format the raw value
     *
     * @access   public
     */
    function __construct($title, $url, $img = '',  $colname = '', $callback = null)
    {
        $this->_title      = $title;
        $this->_url        = $url;
        $this->_columnName = $colname;
        $this->_image      = $img;

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
     * Set the image
     *
     * @access   public
     * @param    string  $img The image
     */
    function setImage($img)
    {
        $this->_image = $image;
    }

    /**
     * Get the image
     *
     * @access   public
     */
    function getImage()
    {
        return $this->_image;
    }

    /**
     * ActionColumn needs an image?
     *
     * @access   public
     */
    function needsImage()
    {
        return !empty($this->_image);
    }
}
?>