<?php
/*
 * JSEvent.php - JavaScript Event, main class
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
class JSEvent
{
    /*
     * The JSEvent Identifier (onclick, doubleclick, mouseover, etc).
     *
     * @var     string $_ID
     * @access  private
     * @see     SetID (), GetID ()
     */
    var $_id;

    /*
     * The javascript code
     *
     * @var     string $_Code
     * @access  private
     * @see     SetCode (), GetCode ()
     */
    var $_code;

    /*
     * The javascript src file
     *
     * @var     string $_Src
     * @access  private
     * @see     GetSrc ()
     */
    var $_src;

    /*
     * JSEvent constructor
     *
     * @param   string  ID of the Event
     * @param   string  Code of the Event
     */
    function __construct($id, $code, $src = '')
    {
        $this->_id = $id;
        $this->_code = $code;
        $this->_src = $src;

        //      if (substr($this->_code, 0, 11) != 'javascript:') {
        //          $this->_code = "javascript:".$this->_code;
        //      }

    }

    /*
     * Set the Javascript ID
     *
     * @param   string  Javascript ID
     * @access  public
     */
    function setID($id)
    {
        $this->_id = $id;
    }

    /*
     * Get the Javascript ID
     *
     * @access  public
     * @return  string The javascript ID
     */
    function getID()
    {
        return $this->_id;
    }

    /*
     * Set the Javascript Code
     *
     * @param   string  Javascript code
     * @access  public
     */
    function setCode($code)
    {
        $this->_code = $code;
    }

    /*
     * Get the Javascript Code
     *
     * @access  public
     * @return  string The javascript code
     */
    function getCode()
    {
        return $this->_code;
    }

    /*
     * Return a boolean value if the code points to a JS file
     *
     * @access  public
     * @return  boolean Return true if the code points to a JS file
     */
    function needsFile()
    {
        return !empty($this->_src);
    }

    /*
     * If the code is a file, it should return the name of the file
     *
     * @access  public
     * @return  string The javascript filename
     */
    function getSrc()
    {
        return $this->_src;
    }
}
?>