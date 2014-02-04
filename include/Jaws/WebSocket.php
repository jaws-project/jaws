<?php
/**
 * Class to handle the WebSocket
 *
 * @category    WebSocket
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WebSocket
{
    /**
     * Network address
     *
     * @access  private
     * @var     string
     */
    private $address;

    /**
     * Network port
     *
     * @access  private
     * @var     int
     */
    private $port = 2048;

    /**
     * Server socket
     *
     * @access  private
     * @var     resource
     */
    private $socket;


    /**
     * Constructor
     *
     * @access  protected
     * @param   string  $address    Network address
     * @param   int     $port       Network port
     * @return  void
     */
    protected function __construct($address, $port)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * Creates the Jaws_WebSocket instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   string  $address    Network address
     * @param   int     $port       Network port
     * @param   string  $instance   Instance name
     * @return  object returns the instance
     */
    static function getInstance($address = '', $port = 0, $instance = 'default')
    {
        static $objWebSocket = array();
        if (!isset($objWebSocket[$instance])) {
            $calssname = get_called_class();
            $objWebSocket[$instance] = new $calssname($address, $port);
        }

        return $objWebSocket[$instance];
    }


    /**
     * Close the socket
     *
     * @access  public
     * @return  void
     */
    public function close()
    {
        socket_close($this->socket);
    }

}