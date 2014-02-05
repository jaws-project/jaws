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
     * @access  protected
     * @var     string
     */
    protected $address;

    /**
     * Network port
     *
     * @access  protected
     * @var     int
     */
    protected $port = 2048;

    /**
     * Server socket
     *
     * @access  protected
     * @var     resource
     */
    protected $socket;

    /**
     * socket send timeout
     *
     * @access  protected
     * @var     int
     */
    protected $send_timeout = 5;

    /**
     * socket receive timeout
     *
     * @access  protected
     * @var     int
     */
    protected $receive_timeout = 15;


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
     * @return  object  returns the instance of Jaws_WebSocket or Jaws_Error on failure
     */
    static function getInstance($address = '', $port = 0, $instance = 'default')
    {
        if (!extension_loaded('sockets')) {
            return Jaws_Error::raiseError('sockets extension is not available');
        }

        static $objWebSocket = array();
        if (!isset($objWebSocket[$instance])) {
            $calssname = get_called_class();
            $objWebSocket[$instance] = new $calssname($address, $port);
        }

        return $objWebSocket[$instance];
    }


    /**
     * Encodes WebSocket sending data
     *
     * @access  protected
     * @param   string  $data   Sending data
     * @return  string  Encoded data
     */
    protected function encode($data)
    {
        $firstByte = 0x80 | (0x1 & 0x0f);
        $length = strlen($data);
        if($length <= 125) {
            $header = pack('CC', $firstByte, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCS', $firstByte, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCN', $firstByte, 127, $length);
        }

        return $header . $data;
    }


    /**
     * Decodes WebSocket received data
     *
     * @access  protected
     * @param   string  $data   Received data
     * @return  string  Decoded data
     */
    protected function decode($data)
    {
        $result = '';
        if (!empty($data)) {
            $length = ord($data[1]) & 127;
            $dataPos = ($length == 126) ? 4 : (($length == 127)? 10 : 2);

            if ($masked = (bool)(ord($data[1]) >> 7)) {
                $mask = substr($data, $dataPos, 4);
                $data = substr($data, $dataPos + 4);

                for ($i = 0; $i < strlen($data); ++$i) {
                    $result.= $data[$i] ^ $mask[$i%4];
                }
            } else {
                $result = substr($data, $dataPos);
            }
        }

        return $result;
    }


    /**
     * Close the socket
     *
     * @access  public
     * @param   resource    $socket socket resource
     * @param   string      $errstr optional error message
     * @return  mixed       True on success or Jaws_Error for socket last error
     */
    public function close($socket = null, $errstr = '')
    {
        if (!empty($socket)) {
            $errno = socket_last_error($socket);
            socket_close($socket);
        } else {
            $errno = socket_last_error();
        }

        if (!empty($errno) || !empty($errstr)) {
            return Jaws_Error::raiseError(empty($errno)? $errstr : socket_strerror($errno));
        }

        return true;
    }

}